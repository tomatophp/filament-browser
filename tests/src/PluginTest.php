<?php

use Illuminate\Support\Facades\Artisan;
use TomatoPHP\FilamentBrowser\Pages\Browser;

it('registers the plugin, the browser page and the developer gate', function () {
    $panel = filament()->getPanel('admin');

    expect($panel->hasPlugin('filament-browser'))->toBeTrue()
        ->and($panel->hasPlugin('filament-developer-gate'))->toBeTrue()
        ->and($panel->getPages())->toContain(Browser::class)
        ->and(Browser::getUrl())->toEndWith('/admin/browser');
});

it('merges the package config', function () {
    expect(config('filament-browser.start_path'))->not->toBeEmpty();
});

it('hides env files by default', function () {
    expect(browserPlugin()->hideEnvFiles)->toBeTrue()
        ->and(browserPlugin()->useDeveloperGate)->toBeTrue();
});

it('runs the install command', function () {
    expect(Artisan::call('filament-browser:install'))->toBe(0);
});
