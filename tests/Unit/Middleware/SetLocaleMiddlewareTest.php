<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected SetLocale $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetLocale();
    }

    public function test_sets_locale_from_url_parameter(): void
    {
        $request = Request::create('/', 'GET', ['locale' => 'es']);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('es', App::getLocale());
            return response('ok');
        });
    }

    public function test_ignores_invalid_locale(): void
    {
        $request = Request::create('/', 'GET', ['locale' => 'invalid']);
        
        $this->middleware->handle($request, function ($req) {
            // Should use default locale
            $this->assertEquals('en', App::getLocale());
            return response('ok');
        });
    }

    public function test_uses_default_locale_when_no_preference(): void
    {
        $request = Request::create('/', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());
            return response('ok');
        });
    }
}
