<?php

namespace Tests\Unit\Services;

use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationService $translationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translationService = new TranslationService();
    }

    public function test_returns_same_text_when_source_and_target_are_same(): void
    {
        $text = 'Hello World';
        $result = $this->translationService->translate($text, 'en', 'en');
        
        $this->assertEquals($text, $result);
    }

    public function test_get_supported_languages_returns_array(): void
    {
        $languages = $this->translationService->getSupportedLanguages();
        
        $this->assertIsArray($languages);
        $this->assertContains('en', $languages);
        $this->assertContains('es', $languages);
        $this->assertContains('fr', $languages);
        $this->assertContains('de', $languages);
    }

    public function test_translate_batch_returns_array(): void
    {
        $texts = [
            'hello' => 'Hello',
            'world' => 'World',
        ];

        // Since we can't make real API calls in tests, we'll just verify
        // that the method returns an array with the same keys
        $result = $this->translationService->translateBatch($texts, 'en', 'en');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hello', $result);
        $this->assertArrayHasKey('world', $result);
    }
}
