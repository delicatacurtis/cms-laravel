<?php

namespace Tests\Unit\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_locale_returns_current_locale(): void
    {
        App::setLocale('es');
        $this->assertEquals('es', current_locale());
    }

    public function test_supported_locales_returns_array(): void
    {
        $locales = supported_locales();
        
        $this->assertIsArray($locales);
        $this->assertContains('en', $locales);
    }

    public function test_locale_name_returns_display_name(): void
    {
        $this->assertEquals('English', locale_name('en'));
        $this->assertEquals('Español', locale_name('es'));
        $this->assertEquals('Français', locale_name('fr'));
        $this->assertEquals('Deutsch', locale_name('de'));
    }

    public function test_is_locale_supported_validates_locale(): void
    {
        $this->assertTrue(is_locale_supported('en'));
        $this->assertTrue(is_locale_supported('es'));
        $this->assertFalse(is_locale_supported('invalid'));
    }

    public function test_switch_locale_changes_locale(): void
    {
        $result = switch_locale('fr');
        
        $this->assertTrue($result);
        $this->assertEquals('fr', App::getLocale());
    }

    public function test_switch_locale_rejects_invalid_locale(): void
    {
        $result = switch_locale('invalid');
        
        $this->assertFalse($result);
    }

    public function test_locale_url_adds_locale_parameter(): void
    {
        $url = locale_url('/page', 'es');
        
        $this->assertStringContainsString('locale=es', $url);
    }
}
