<?php

if (!function_exists('current_locale')) {
    /**
     * Get the current application locale
     *
     * @return string
     */
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales
     *
     * @return array
     */
    function supported_locales(): array
    {
        return config('app.supported_locales', ['en', 'es', 'fr', 'de']);
    }
}

if (!function_exists('locale_name')) {
    /**
     * Get the display name for a locale
     *
     * @param string|null $locale
     * @return string
     */
    function locale_name(?string $locale = null): string
    {
        $locale = $locale ?? current_locale();
        
        $names = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
        ];

        return $names[$locale] ?? $locale;
    }
}

if (!function_exists('is_locale_supported')) {
    /**
     * Check if a locale is supported
     *
     * @param string $locale
     * @return bool
     */
    function is_locale_supported(string $locale): bool
    {
        return in_array($locale, supported_locales());
    }
}

if (!function_exists('switch_locale')) {
    /**
     * Switch the application locale
     *
     * @param string $locale
     * @return bool
     */
    function switch_locale(string $locale): bool
    {
        if (!is_locale_supported($locale)) {
            return false;
        }

        app()->setLocale($locale);
        session()->put('locale', $locale);

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return true;
    }
}

if (!function_exists('locale_url')) {
    /**
     * Generate a URL with locale parameter
     *
     * @param string $path
     * @param string|null $locale
     * @return string
     */
    function locale_url(string $path, ?string $locale = null): string
    {
        $locale = $locale ?? current_locale();
        $separator = str_contains($path, '?') ? '&' : '?';
        
        return $path . $separator . 'locale=' . $locale;
    }
}

if (!function_exists('translate_auto')) {
    /**
     * Automatically translate text to current locale
     *
     * @param string $text
     * @param string $sourceLang
     * @return string
     */
    function translate_auto(string $text, string $sourceLang = 'en'): string
    {
        $translationService = app(\App\Services\TranslationService::class);
        return $translationService->translate($text, current_locale(), $sourceLang);
    }
}
