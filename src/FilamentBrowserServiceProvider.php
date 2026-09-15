<?php

namespace TomatoPHP\FilamentBrowser;

use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentBrowser\Console\FilamentBrowserInstall;

class FilamentBrowserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            FilamentBrowserInstall::class,
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/filament-browser.php', 'filament-browser');

        $this->publishes([
            __DIR__.'/../config/filament-browser.php' => config_path('filament-browser.php'),
        ], 'filament-browser-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-browser');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/filament-browser'),
        ], 'filament-browser-views');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-browser');

        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-browser'),
        ], 'filament-browser-lang');
    }

    public function boot(): void
    {
        //
    }
}
