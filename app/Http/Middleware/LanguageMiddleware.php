<?php

namespace App\Http\Middleware;

use App\Services\Helpers\LanguageService;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class LanguageMiddleware
{
    /**
     * LanguageMiddleware constructor.
     */
    public function __construct(private Application $app) {}

    /**
     * Handle an incoming request and set the user's preferred language.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $language = $request->user()?->language ?? $request->session()?->get('locale');
        // A language the admin turned off falls back to the panel default.
        $this->app->setLocale($language && app(LanguageService::class)->isEnabled($language) ? $language : config('app.locale', 'en'));

        return $next($request);
    }
}
