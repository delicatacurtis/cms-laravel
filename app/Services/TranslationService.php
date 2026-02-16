<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl = 'https://api.mymemory.translated.net/get';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.mymemory.api_key', '');
    }

    /**
     * Translate text from source language to target language
     *
     * @param string $text The text to translate
     * @param string $targetLang Target language code (en, es, fr, de)
     * @param string $sourceLang Source language code (default: en)
     * @return string Translated text
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'en'): string
    {
        // Don't translate if source and target are the same
        if ($sourceLang === $targetLang) {
            return $text;
        }

        // Create cache key
        $cacheKey = $this->getCacheKey($text, $sourceLang, $targetLang);

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $translation = $this->fetchTranslation($text, $sourceLang, $targetLang);
            
            // Cache the translation for 30 days
            Cache::put($cacheKey, $translation, now()->addDays(30));
            
            return $translation;
        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'text' => $text,
                'source' => $sourceLang,
                'target' => $targetLang,
                'error' => $e->getMessage(),
            ]);
            
            // Return original text if translation fails
            return $text;
        }
    }

    /**
     * Translate an array of strings
     *
     * @param array $texts Array of texts to translate
     * @param string $targetLang Target language code
     * @param string $sourceLang Source language code
     * @return array Translated texts
     */
    public function translateBatch(array $texts, string $targetLang, string $sourceLang = 'en'): array
    {
        $translations = [];
        
        foreach ($texts as $key => $text) {
            $translations[$key] = $this->translate($text, $targetLang, $sourceLang);
            
            // Add small delay to avoid rate limiting
            usleep(100000); // 100ms delay
        }
        
        return $translations;
    }

    /**
     * Fetch translation from MyMemory API
     *
     * @param string $text Text to translate
     * @param string $sourceLang Source language
     * @param string $targetLang Target language
     * @return string Translated text
     */
    protected function fetchTranslation(string $text, string $sourceLang, string $targetLang): string
    {
        $params = [
            'q' => $text,
            'langpair' => "{$sourceLang}|{$targetLang}",
        ];

        // Add API key if available
        if (!empty($this->apiKey)) {
            $params['key'] = $this->apiKey;
        }

        $response = $this->client->get($this->apiUrl, [
            'query' => $params,
            'timeout' => 10,
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['responseData']['translatedText'])) {
            return $data['responseData']['translatedText'];
        }

        throw new \Exception('Translation not found in response');
    }

    /**
     * Get cache key for translation
     *
     * @param string $text Text to translate
     * @param string $sourceLang Source language
     * @param string $targetLang Target language
     * @return string Cache key
     */
    protected function getCacheKey(string $text, string $sourceLang, string $targetLang): string
    {
        return 'translation_' . md5($text . $sourceLang . $targetLang);
    }

    /**
     * Clear translation cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Get supported languages
     *
     * @return array Array of supported language codes
     */
    public function getSupportedLanguages(): array
    {
        return config('app.supported_locales', ['en', 'es', 'fr', 'de']);
    }
}
