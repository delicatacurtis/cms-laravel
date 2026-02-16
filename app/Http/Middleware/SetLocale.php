<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);
        
        if ($this->isValidLocale($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale from request
     *
     * @param Request $request
     * @return string
     */
    protected function getLocale(Request $request): string
    {
        // Priority 1: URL parameter
        if ($request->has('locale') && $this->isValidLocale($request->input('locale'))) {
            $locale = $request->input('locale');
            Session::put('locale', $locale);
            
            // Update user preference if authenticated
            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
            
            return $locale;
        }

        // Priority 2: User preference (if authenticated)
        if ($request->user() && $request->user()->locale) {
            return $request->user()->locale;
        }

        // Priority 3: Session
        if (Session::has('locale')) {
            return Session::get('locale');
        }

        // Priority 4: Browser language
        $browserLang = $request->getPreferredLanguage($this->getSupportedLocales());
        if ($browserLang) {
            return $browserLang;
        }

        // Priority 5: Default locale
        return config('app.locale', 'en');
    }

    /**
     * Check if locale is valid
     *
     * @param string|null $locale
     * @return bool
     */
    protected function isValidLocale(?string $locale): bool
    {
        if (!$locale) {
            return false;
        }

        return in_array($locale, $this->getSupportedLocales());
    }

    /**
     * Get supported locales
     *
     * @return array
     */
    protected function getSupportedLocales(): array
    {
        return config('app.supported_locales', ['en', 'es', 'fr', 'de']);
    }
}
