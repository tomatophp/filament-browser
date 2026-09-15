<?php

use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;
use TomatoPHP\FilamentBrowser\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function browserPlugin(): FilamentBrowserPlugin
{
    /** @var FilamentBrowserPlugin */
    return filament()->getPanel('admin')->getPlugin('filament-browser');
}

/**
 * Create a temp folder: <base>/outside.txt (must never be reachable) and <base>/root (the browser root).
 */
function makeBrowserFixture(): string
{
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'filament-browser-'.uniqid();

    File::makeDirectory($base.'/root/docs', 0755, true);
    File::put($base.'/outside.txt', 'top secret');
    File::put($base.'/root/readme.txt', 'hello');
    File::put($base.'/root/notes.md', '# Notes');
    File::put($base.'/root/.env', 'APP_KEY=secret');
    File::put($base.'/root/data.csv', "name,qty\nApple,3\n");
    File::put($base.'/root/docs/guide.txt', 'guide');

    return realpath($base);
}

function unlockDeveloperGate(): void
{
    session()->put('developer_password', config('filament-developer-gate.password'));
}
