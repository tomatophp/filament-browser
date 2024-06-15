<?php

namespace TomatoPHP\FilamentBrowser;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\View\View;
use TomatoPHP\FilamentBrowser\Pages\Browser;
use TomatoPHP\FilamentDeveloperGate\FilamentDeveloperGatePlugin;
use TomatoPHP\FilamentDeveloperGate\Pages\DeveloperGate;

class FilamentBrowserPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-browser';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->plugin(FilamentDeveloperGatePlugin::make())
            ->pages([
                Browser::class
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
