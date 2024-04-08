<?php

namespace TomatoPHP\FilamentBrowser;

use Illuminate\Support\ServiceProvider;


class FilamentBrowserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register generate command
        $this->commands([
           \TomatoPHP\FilamentBrowser\Console\FilamentBrowserInstall::class,
        ]);

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-browser.php', 'filament-browser');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-browser.php' => config_path('filament-browser.php'),
        ], 'filament-browser-config');

        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //Publish Migrations
        $this->publishes([
           __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-browser-migrations');
        //Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-browser');

        //Publish Views
        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-browser'),
        ], 'filament-browser-views');

        //Register Langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-browser');

        //Publish Lang
        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-browser'),
        ], 'filament-browser-lang');

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
    }

    public function boot(): void
    {
        //you boot methods here
    }
}
