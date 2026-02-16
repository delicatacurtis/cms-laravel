# Multi-Language Support Documentation

## Overview

This Laravel CMS now includes comprehensive multi-language support with automated translations, allowing your content to be available in multiple languages with minimal effort.

## Supported Languages

The system currently supports the following languages:
- **English (en)** - Default language
- **Spanish (es)**
- **French (fr)**
- **German (de)**

## Features

### 1. Automatic Translation Service

The `TranslationService` uses the MyMemory Translation API to automatically translate content between supported languages.

**Key Features:**
- Automatic translation caching (30 days)
- Rate limiting protection
- Batch translation support
- Fallback to original text on API failure

### 2. Locale Detection Middleware

The `SetLocale` middleware automatically detects and sets the appropriate language based on:
1. URL parameter (`?locale=es`)
2. User preference (for authenticated users)
3. Session storage
4. Browser language preferences
5. Default application locale

### 3. Language Switcher Component

A Livewire component that provides an easy-to-use dropdown for switching between languages.

**Usage in Blade templates:**
```blade
<livewire:language-switcher />
```

### 4. Artisan Command for Translation Generation

Generate translations automatically from your source language files.

**Basic Usage:**
```bash
# Translate all files from English to all other languages
php artisan translate:generate

# Translate to specific languages
php artisan translate:generate --target=es,fr

# Translate a specific file
php artisan translate:generate --file=messages

# Force overwrite existing translations
php artisan translate:generate --force

# Translate from a different source language
php artisan translate:generate --source=es --target=en
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Supported locales (comma-separated)
SUPPORTED_LOCALES=en,es,fr,de

# MyMemory API Key (optional, but recommended for higher limits)
MYMEMORY_API_KEY=your_api_key_here
```

To get a free MyMemory API key:
1. Visit https://mymemory.translated.net/doc/
2. Sign up for a free account
3. Get your API key from your account dashboard

### Configuration Files

**config/app.php:**
```php
'locale' => 'en',
'supported_locales' => explode(',', env('SUPPORTED_LOCALES', 'en,es,fr,de')),
```

**config/services.php:**
```php
'mymemory' => [
    'api_key' => env('MYMEMORY_API_KEY', ''),
],
```

## Helper Functions

The system includes several helper functions for locale management:

### `current_locale()`
Get the current application locale.
```php
$locale = current_locale(); // Returns 'en', 'es', etc.
```

### `supported_locales()`
Get array of all supported locales.
```php
$locales = supported_locales(); // Returns ['en', 'es', 'fr', 'de']
```

### `locale_name($locale)`
Get the display name for a locale.
```php
$name = locale_name('es'); // Returns 'Español'
```

### `is_locale_supported($locale)`
Check if a locale is supported.
```php
if (is_locale_supported('es')) {
    // Locale is supported
}
```

### `switch_locale($locale)`
Programmatically switch the application locale.
```php
switch_locale('fr'); // Changes to French
```

### `locale_url($path, $locale)`
Generate a URL with locale parameter.
```php
$url = locale_url('/contact', 'es'); // Returns '/contact?locale=es'
```

### `translate_auto($text, $sourceLang)`
Automatically translate text to current locale.
```php
$translated = translate_auto('Hello', 'en');
```

## Usage Examples

### In Blade Templates

**Using translations:**
```blade
<h1>{{ __('messages.welcome') }}</h1>
<p>{{ __('messages.home') }}</p>
```

**Current locale:**
```blade
<p>Current language: {{ current_locale() }}</p>
<p>Current language name: {{ locale_name() }}</p>
```

**Locale switcher:**
```blade
<nav>
    <livewire:language-switcher />
</nav>
```

### In Controllers

**Set locale programmatically:**
```php
public function changeLanguage(Request $request)
{
    $locale = $request->input('locale');
    
    if (is_locale_supported($locale)) {
        switch_locale($locale);
        return redirect()->back();
    }
    
    return redirect()->back()->with('error', 'Invalid locale');
}
```

**Use translation service:**
```php
use App\Services\TranslationService;

public function translate(TranslationService $translationService)
{
    $translated = $translationService->translate(
        'Hello World',
        'es', // target
        'en'  // source
    );
    
    return $translated; // Returns 'Hola Mundo'
}
```

### Creating New Translation Files

1. Create a new file in `lang/en/` directory:
```php
// lang/en/navigation.php
return [
    'home' => 'Home',
    'about' => 'About Us',
    'services' => 'Our Services',
    'contact' => 'Contact',
];
```

2. Run the translation command:
```bash
php artisan translate:generate --file=navigation
```

3. Use in templates:
```blade
<a href="/">{{ __('navigation.home') }}</a>
```

## Database Migration

The system adds a `locale` column to the users table to store user language preferences:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('locale', 5)->default('en')->after('email');
});
```

Run the migration:
```bash
php artisan migrate
```

## User Preferences

When a user changes their language preference:
1. The locale is stored in their user record
2. The locale is stored in the session
3. On subsequent visits, their preference is automatically applied

## Filament Admin Panel Integration

The multi-language system is integrated with Filament:
- Admin users can set their preferred language
- The language switcher is available in the admin panel
- All Filament resources respect the current locale

## API Rate Limits

MyMemory API has the following limits:
- **Without API Key:** 1,000 words per day
- **With Free API Key:** 10,000 words per day
- **With Premium Account:** Higher limits available

To avoid hitting limits:
- Translations are cached for 30 days
- The system includes built-in rate limiting protection
- Use the artisan command during off-peak hours for bulk translations

## Translation Quality

For best translation quality:
1. Use clear, simple language in your source translations
2. Avoid idioms and colloquialisms
3. Keep sentences reasonably short
4. Review automated translations for critical content
5. Consider professional translation services for marketing materials

## Troubleshooting

### Translations not appearing
- Ensure language files exist in `lang/{locale}/` directory
- Clear cache: `php artisan cache:clear`
- Check locale is set correctly: `dd(app()->getLocale())`

### API errors
- Verify your API key in `.env`
- Check daily rate limit hasn't been exceeded
- Ensure internet connectivity

### Middleware not working
- Verify middleware is registered in `app/Http/Kernel.php`
- Check middleware is in the 'web' group
- Clear config cache: `php artisan config:clear`

## Best Practices

1. **Always cache translations:** The system does this automatically, but avoid clearing cache frequently
2. **Use translation keys:** Don't hardcode text, use `__()` helper
3. **Test all locales:** Verify your application works in all supported languages
4. **Provide fallbacks:** Always have English translations as fallback
5. **User preferences:** Respect user language preferences when available

## Testing

Run the translation tests:
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test tests/Unit/Services/TranslationServiceTest.php
```

## Performance Considerations

- Translations are cached to minimize API calls
- Middleware adds minimal overhead (~1-2ms per request)
- Language files are loaded only when needed
- Consider using Redis for session/cache in production

## Future Enhancements

Potential improvements to consider:
- Add more languages
- Implement translation management UI in admin panel
- Add support for right-to-left (RTL) languages
- Implement content translation (not just UI)
- Add translation approval workflow
- Support pluralization rules per locale

## Support

For issues or questions:
- Check Laravel localization docs: https://laravel.com/docs/localization
- MyMemory API docs: https://mymemory.translated.net/doc/
- File an issue in the project repository
