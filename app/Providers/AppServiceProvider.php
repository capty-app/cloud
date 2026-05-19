<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureUrls();
    }

    /**
     * Make URL generation respect APP_URL. When APP_URL is https://… all
     * generated asset/route URLs use HTTPS even if the request arrived as
     * plain HTTP (e.g. behind a TLS-terminating proxy). When APP_URL is
     * http://…, URLs stay HTTP — no scheme is forced.
     */
    protected function configureUrls(): void
    {
        $appUrl = (string) config('app.url');
        if ($appUrl === '') {
            return;
        }

        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        if ($scheme === 'https') {
            URL::forceScheme('https');
        }

        $host = parse_url($appUrl, PHP_URL_HOST);
        if ($host) {
            URL::forceRootUrl($appUrl);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
