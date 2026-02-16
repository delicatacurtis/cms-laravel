<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:generate 
                            {--source=en : Source language code}
                            {--target= : Target language code (comma-separated for multiple)}
                            {--file= : Specific translation file to translate}
                            {--force : Overwrite existing translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate translations automatically using MyMemory API';

    protected TranslationService $translationService;

    /**
     * Execute the console command.
     */
    public function handle(TranslationService $translationService): int
    {
        $this->translationService = $translationService;
        
        $sourceLang = $this->option('source');
        $targetLangs = $this->option('target') 
            ? explode(',', $this->option('target'))
            : array_diff(config('app.supported_locales', ['en', 'es', 'fr', 'de']), [$sourceLang]);
        
        $specificFile = $this->option('file');
        $force = $this->option('force');

        $this->info("Starting translation generation...");
        $this->info("Source language: {$sourceLang}");
        $this->info("Target languages: " . implode(', ', $targetLangs));

        $sourcePath = lang_path($sourceLang);
        
        if (!File::exists($sourcePath)) {
            $this->error("Source language directory does not exist: {$sourcePath}");
            return Command::FAILURE;
        }

        // Get all PHP files in source language directory
        $files = $specificFile 
            ? ["{$specificFile}.php"]
            : File::files($sourcePath);

        foreach ($targetLangs as $targetLang) {
            $this->info("\nTranslating to {$targetLang}...");
            
            foreach ($files as $file) {
                $filename = is_string($file) ? $file : $file->getFilename();
                
                if (!str_ends_with($filename, '.php')) {
                    continue;
                }

                $this->translateFile($sourceLang, $targetLang, $filename, $force);
            }
        }

        $this->info("\n✓ Translation generation completed!");
        
        return Command::SUCCESS;
    }

    /**
     * Translate a specific file
     */
    protected function translateFile(string $sourceLang, string $targetLang, string $filename, bool $force): void
    {
        $sourceFile = lang_path("{$sourceLang}/{$filename}");
        $targetFile = lang_path("{$targetLang}/{$filename}");

        if (!File::exists($sourceFile)) {
            $this->warn("Source file does not exist: {$filename}");
            return;
        }

        // Load source translations
        $sourceTranslations = include $sourceFile;
        
        if (!is_array($sourceTranslations)) {
            $this->warn("Invalid translation file: {$filename}");
            return;
        }

        // Load existing target translations if they exist
        $targetTranslations = [];
        if (File::exists($targetFile) && !$force) {
            $targetTranslations = include $targetFile;
        }

        $this->line("  Processing {$filename}...");
        $progressBar = $this->output->createProgressBar(count($sourceTranslations));
        $progressBar->start();

        // Translate each key
        foreach ($sourceTranslations as $key => $value) {
            // Skip if translation exists and not forcing
            if (isset($targetTranslations[$key]) && !$force) {
                $progressBar->advance();
                continue;
            }

            // Only translate string values
            if (is_string($value)) {
                try {
                    $targetTranslations[$key] = $this->translationService->translate(
                        $value,
                        $targetLang,
                        $sourceLang
                    );
                } catch (\Exception $e) {
                    $this->warn("\n  Failed to translate key '{$key}': {$e->getMessage()}");
                    $targetTranslations[$key] = $value;
                }
            } else {
                $targetTranslations[$key] = $value;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Save translated file
        $this->saveTranslationFile($targetFile, $targetTranslations);
        
        $this->info("  ✓ Saved {$filename} for {$targetLang}");
    }

    /**
     * Save translation array to file
     */
    protected function saveTranslationFile(string $filepath, array $translations): void
    {
        $directory = dirname($filepath);
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        File::put($filepath, $content);
    }
}
