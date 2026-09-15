<?php

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentBrowser\Pages\Browser;
use TomatoPHP\FilamentBrowser\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->base = makeBrowserFixture();
    $this->root = $this->base.DIRECTORY_SEPARATOR.'root';
    browserPlugin()->basePath($this->root);
    actingAs(User::factory()->create());
    unlockDeveloperGate();
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

function openFile(string $path): TestAction
{
    return TestAction::make('openFile')->arguments(['path' => $path]);
}

it('creates a code file when allowed', function () {
    browserPlugin()->allowCreateNewFile();

    livewire(Browser::class)
        ->callAction('create', ['type' => 'file-code', 'name' => 'hello', 'extension' => 'txt', 'code' => 'hi'])
        ->assertHasNoActionErrors();

    expect(File::get($this->root.'/hello.txt'))->toBe('hi');
});

it('creates a folder when allowed', function () {
    browserPlugin()->allowCreateFolder();

    livewire(Browser::class)
        ->callAction('create', ['type' => 'folder', 'name' => 'new-folder'])
        ->assertHasNoActionErrors();

    expect(is_dir($this->root.'/new-folder'))->toBeTrue();
});

it('hides the create action when nothing may be created', function () {
    livewire(Browser::class)->assertActionHidden('create');
});

it('refuses a create type that is not allowed', function () {
    browserPlugin()->allowCreateNewFile();

    livewire(Browser::class)
        ->callAction('create', ['type' => 'folder', 'name' => 'new-folder']);

    expect(is_dir($this->root.'/new-folder'))->toBeFalse();
});

it('refuses to create a file outside the root', function () {
    browserPlugin()->allowCreateNewFile()->allowCreateFolder();

    livewire(Browser::class)
        ->callAction('create', ['type' => 'file-code', 'name' => '../evil', 'extension' => 'txt', 'code' => 'x'])
        ->callAction('create', ['type' => 'folder', 'name' => '../evil-folder'])
        ->assertNotified(trans('filament-browser::messages.notifications.refused'));

    expect(File::exists($this->base.'/evil.txt'))->toBeFalse()
        ->and(is_dir($this->base.'/evil-folder'))->toBeFalse();
});

it('navigates into a folder and back', function () {
    livewire(Browser::class)
        ->callAction(TestAction::make('openFolder')->arguments(['path' => 'docs']))
        ->assertSee('guide.txt')
        ->assertDontSee('readme.txt')
        ->callAction('back')
        ->assertSee('readme.txt');
});

it('refuses to navigate outside the root', function () {
    livewire(Browser::class)
        ->callAction(TestAction::make('openFolder')->arguments(['path' => '..']))
        ->assertNotified(trans('filament-browser::messages.notifications.refused'))
        ->assertDontSee('outside.txt');

    expect(session('filament-browser-path', ''))->toBe('');
});

it('ignores a tampered session path', function () {
    session()->put('filament-browser-path', '..');

    livewire(Browser::class)
        ->assertSee('readme.txt')
        ->assertDontSee('outside.txt');
});

it('opens a file with its content', function () {
    livewire(Browser::class)
        ->mountAction(openFile('readme.txt'))
        ->assertActionMounted(openFile('readme.txt'))
        ->assertSchemaStateSet(['content' => 'hello']);
});

it('edits a file when allowed', function () {
    browserPlugin()->allowEditFile();

    livewire(Browser::class)
        ->callAction(openFile('readme.txt'), ['content' => 'changed'])
        ->assertHasNoActionErrors();

    expect(File::get($this->root.'/readme.txt'))->toBe('changed');
});

it('does not save a file when editing is not allowed', function () {
    livewire(Browser::class)
        ->callAction(openFile('readme.txt'), ['content' => 'changed']);

    expect(File::get($this->root.'/readme.txt'))->toBe('hello');
});

it('refuses to open or write files outside the root', function (string $path) {
    browserPlugin()->allowEditFile();

    livewire(Browser::class)
        ->callAction(openFile($path), ['content' => 'pwned'])
        ->assertNotified(trans('filament-browser::messages.notifications.refused'));

    expect(File::get($this->base.'/outside.txt'))->toBe('top secret')
        ->and(File::get($this->root.'/.env'))->toBe('APP_KEY=secret');
})->with(['../outside.txt', 'docs/../../outside.txt', '.env']);

it('renames a file when allowed', function () {
    browserPlugin()->allowRenameFile();

    livewire(Browser::class)
        ->callAction([openFile('readme.txt'), TestAction::make('renameFile')], ['name' => 'renamed.txt']);

    expect(File::exists($this->root.'/renamed.txt'))->toBeTrue()
        ->and(File::exists($this->root.'/readme.txt'))->toBeFalse();
});

it('refuses to rename a file outside the root', function () {
    browserPlugin()->allowRenameFile();

    livewire(Browser::class)
        ->callAction([openFile('readme.txt'), TestAction::make('renameFile')], ['name' => '../moved.txt']);

    expect(File::exists($this->base.'/moved.txt'))->toBeFalse()
        ->and(File::exists($this->root.'/readme.txt'))->toBeTrue();
});

it('hides rename and delete when not allowed', function () {
    livewire(Browser::class)
        ->assertActionHidden([openFile('readme.txt'), TestAction::make('renameFile')])
        ->assertActionHidden([openFile('readme.txt'), TestAction::make('deleteFile')]);
});

it('deletes a file when allowed', function () {
    browserPlugin()->allowDeleteFile();

    livewire(Browser::class)
        ->callAction([openFile('readme.txt'), TestAction::make('deleteFile')]);

    expect(File::exists($this->root.'/readme.txt'))->toBeFalse();
});

it('previews spreadsheets', function () {
    $component = livewire(Browser::class)
        ->mountAction(openFile('data.csv'))
        ->assertActionMounted(openFile('data.csv'));

    expect($component->instance()->buildPreview($this->root.DIRECTORY_SEPARATOR.'data.csv'))
        ->toBe(['kind' => 'sheet', 'rows' => [['name', 'qty'], ['Apple', 3]]]);
});

it('previews images inline without copying them to public storage', function () {
    File::put($this->root.'/pixel.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

    $preview = livewire(Browser::class)->instance()->buildPreview($this->root.DIRECTORY_SEPARATOR.'pixel.png');

    expect($preview['kind'])->toBe('image')
        ->and($preview['src'])->toStartWith('data:image/png;base64,')
        ->and(File::exists(storage_path('app/public/tmp')))->toBeFalse();
});
