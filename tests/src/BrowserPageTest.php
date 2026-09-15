<?php

use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentBrowser\Pages\Browser;
use TomatoPHP\FilamentBrowser\Tests\Models\User;
use TomatoPHP\FilamentDeveloperGate\Pages\DeveloperGate;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->base = makeBrowserFixture();
    browserPlugin()->basePath($this->base.DIRECTORY_SEPARATOR.'root');
    actingAs(User::factory()->create());
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

it('redirects to the developer gate while locked', function () {
    get(Browser::getUrl())->assertRedirect(DeveloperGate::getUrl());
});

it('renders for an authenticated developer once the gate is unlocked', function () {
    unlockDeveloperGate();

    get(Browser::getUrl())
        ->assertOk()
        ->assertSee('readme.txt')
        ->assertSee('docs')
        ->assertDontSee('outside.txt');
});

it('refuses livewire requests while the gate is locked', function () {
    livewire(Browser::class)->assertForbidden();
});

it('renders with the developer gate disabled', function () {
    browserPlugin()->developerGate(false);

    livewire(Browser::class)
        ->assertSuccessful()
        ->assertSee('readme.txt')
        ->assertSee('notes.md');
});

it('is forbidden when the authorize callback denies access', function () {
    browserPlugin()->developerGate(false)->authorize(fn () => false);

    livewire(Browser::class)->assertForbidden();
});

it('redirects guests to the login page', function () {
    auth()->logout();

    get(Browser::getUrl())->assertRedirect();
});
