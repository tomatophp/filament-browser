<?php

use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentBrowser\Models\Files;
use TomatoPHP\FilamentBrowser\Services\FileBrowser;

beforeEach(function () {
    $this->base = makeBrowserFixture();
    $this->root = $this->base.DIRECTORY_SEPARATOR.'root';
    browserPlugin()->basePath($this->root);
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

it('lists the configured root with the Files model', function () {
    $names = Files::all()->pluck('name')->all();

    expect($names)->toContain('readme.txt', 'notes.md', 'data.csv', 'docs')
        ->not->toContain('.env', 'outside.txt');
});

it('lists a sub folder by relative path', function () {
    expect(array_keys(FileBrowser::make()->list('docs')))->toBe(['docs/guide.txt']);
});

it('resolves paths inside the root', function () {
    $browser = FileBrowser::make();

    expect($browser->resolve(''))->toBe($this->root)
        ->and($browser->resolveFile('readme.txt'))->toBe($this->root.DIRECTORY_SEPARATOR.'readme.txt')
        ->and($browser->resolveDirectory('docs'))->toBe($this->root.DIRECTORY_SEPARATOR.'docs');
});

it('refuses paths that escape the root', function (string $path) {
    expect(FileBrowser::make()->resolve($path))->toBeNull();
})->with([
    'parent' => '..',
    'parent file' => '../outside.txt',
    'nested traversal' => 'docs/../../outside.txt',
    'dot segment' => './readme.txt',
    'windows separators' => '..\\outside.txt',
    'absolute unix' => '/etc/passwd',
    'absolute windows' => 'C:\\Windows\\win.ini',
    'null byte' => "readme.txt\0.png",
    'missing' => 'nope.txt',
]);

it('refuses the absolute path of a file outside the root', function () {
    expect(FileBrowser::make()->resolve($this->base.DIRECTORY_SEPARATOR.'outside.txt'))->toBeNull();
});

it('hides env files unless explicitly shown', function () {
    expect(FileBrowser::make()->resolve('.env'))->toBeNull()
        ->and(FileBrowser::make()->list())->not->toHaveKey('.env');

    browserPlugin()->hideEnvFiles(false);

    expect(FileBrowser::make()->resolve('.env'))->not->toBeNull()
        ->and(FileBrowser::make()->list())->toHaveKey('.env');
});

it('hides configured files, folders and extensions', function () {
    browserPlugin()
        ->hiddenFiles([$this->root.'/readme.txt'])
        ->hiddenFolders([$this->root.'/docs'])
        ->hiddenExtensions(['csv']);

    $browser = FileBrowser::make();

    expect($browser->resolve('readme.txt'))->toBeNull()
        ->and($browser->resolve('docs'))->toBeNull()
        ->and($browser->resolve('docs/guide.txt'))->toBeNull()
        ->and($browser->resolve('data.csv'))->toBeNull()
        ->and(array_keys($browser->list()))->toBe(['notes.md']);
});

it('refuses unsafe names for new entries', function (string $name) {
    expect(FileBrowser::make()->target('', $name))->toBeNull();
})->with(['..', '.', '../evil.txt', 'a/b.txt', 'a\\b.txt', '.env', 'c:evil.txt', '']);

it('does not follow symlinks out of the root', function () {
    symlink($this->base, $this->root.DIRECTORY_SEPARATOR.'escape');

    expect(FileBrowser::make()->resolve('escape/outside.txt'))->toBeNull()
        ->and(FileBrowser::make()->list())->not->toHaveKey('escape');
})->skip(PHP_OS_FAMILY === 'Windows', 'Creating symlinks needs elevated rights on Windows.');
