<?php

namespace Tests\Feature\Livewire;

use App\Livewire\LanguageSwitcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        Livewire::test(LanguageSwitcher::class)
            ->assertStatus(200);
    }

    public function test_displays_current_locale(): void
    {
        app()->setLocale('es');
        
        Livewire::test(LanguageSwitcher::class)
            ->assertSet('currentLocale', 'es');
    }

    public function test_displays_all_available_locales(): void
    {
        Livewire::test(LanguageSwitcher::class)
            ->assertSet('availableLocales', ['en', 'es', 'fr', 'de']);
    }

    public function test_can_switch_language(): void
    {
        Livewire::test(LanguageSwitcher::class)
            ->call('switchLanguage', 'fr')
            ->assertRedirect();
    }

    public function test_ignores_invalid_locale(): void
    {
        $initialLocale = app()->getLocale();
        
        Livewire::test(LanguageSwitcher::class)
            ->call('switchLanguage', 'invalid')
            ->assertSet('currentLocale', $initialLocale);
    }
}
