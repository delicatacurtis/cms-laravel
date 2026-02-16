<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public string $currentLocale;
    public array $availableLocales;

    public function mount()
    {
        $this->currentLocale = App::getLocale();
        $this->availableLocales = config('app.supported_locales', ['en', 'es', 'fr', 'de']);
    }

    public function switchLanguage($locale)
    {
        if (!in_array($locale, $this->availableLocales)) {
            return;
        }

        // Store in session
        Session::put('locale', $locale);

        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        // Set current locale
        App::setLocale($locale);
        $this->currentLocale = $locale;

        // Emit event to refresh page
        $this->dispatch('locale-changed', locale: $locale);
        
        // Refresh the page to apply the new locale
        return redirect()->to(request()->url());
    }

    public function getLocaleName($locale)
    {
        $names = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
        ];

        return $names[$locale] ?? $locale;
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
