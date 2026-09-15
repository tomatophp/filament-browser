<?php

namespace TomatoPHP\FilamentBrowser;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentBrowser\Pages\Browser;
use TomatoPHP\FilamentDeveloperGate\FilamentDeveloperGatePlugin;

class FilamentBrowserPlugin implements Plugin
{
    public bool $allowUpload = false;

    public bool $allowCreateNewFile = false;

    public bool $allowCreateFolder = false;

    public bool $allowDeleteFile = false;

    public bool $allowRenameFile = false;

    public bool $allowEditFile = false;

    public bool $allowMarkdown = false;

    public bool $allowCode = false;

    public bool $allowPreview = true;

    public bool $hideEnvFiles = true;

    public bool $useDeveloperGate = true;

    public bool|Closure $authorizeUsing = true;

    /** @var array<int, string> */
    public array $hiddenFiles = [];

    /** @var array<int, string> */
    public array $hiddenExtensions = [];

    /** @var array<int, string> */
    public array $hiddenFolders = [];

    public string $basePath = '';

    public function getId(): string
    {
        return 'filament-browser';
    }

    /**
     * @param  array<int, string>  $files  absolute paths
     */
    public function hiddenFiles(array $files): static
    {
        $this->hiddenFiles = $files;

        return $this;
    }

    /**
     * @param  array<int, string>  $extensions
     */
    public function hiddenExtensions(array $extensions): static
    {
        $this->hiddenExtensions = $extensions;

        return $this;
    }

    /**
     * @param  array<int, string>  $folders  absolute paths
     */
    public function hiddenFolders(array $folders): static
    {
        $this->hiddenFolders = $folders;

        return $this;
    }

    /**
     * `.env` and `.env.*` files are hidden by default; pass false to show them.
     */
    public function hideEnvFiles(bool $condition = true): static
    {
        $this->hideEnvFiles = $condition;

        return $this;
    }

    public function allowRenameFile(bool $condition = true): static
    {
        $this->allowRenameFile = $condition;

        return $this;
    }

    public function allowDeleteFile(bool $condition = true): static
    {
        $this->allowDeleteFile = $condition;

        return $this;
    }

    public function allowUpload(bool $condition = true): static
    {
        $this->allowUpload = $condition;

        return $this;
    }

    public function allowCreateNewFile(bool $condition = true): static
    {
        $this->allowCreateNewFile = $condition;

        return $this;
    }

    public function allowCreateFolder(bool $condition = true): static
    {
        $this->allowCreateFolder = $condition;

        return $this;
    }

    public function allowEditFile(bool $condition = true): static
    {
        $this->allowEditFile = $condition;

        return $this;
    }

    public function allowMarkdown(bool $condition = true): static
    {
        $this->allowMarkdown = $condition;

        return $this;
    }

    public function allowCode(bool $condition = true): static
    {
        $this->allowCode = $condition;

        return $this;
    }

    public function allowPreview(bool $condition = true): static
    {
        $this->allowPreview = $condition;

        return $this;
    }

    /**
     * Protect the browser with tomatophp/filament-developer-gate (enabled by default).
     */
    public function developerGate(bool $condition = true): static
    {
        $this->useDeveloperGate = $condition;

        return $this;
    }

    /**
     * Restrict who can open the browser, e.g. fn () => auth()->user()->isSuperAdmin().
     */
    public function authorize(bool|Closure $callback): static
    {
        $this->authorizeUsing = $callback;

        return $this;
    }

    public function isAuthorized(): bool
    {
        return (bool) value($this->authorizeUsing);
    }

    public function basePath(string $path): static
    {
        $this->basePath = $path;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->useDeveloperGate && ! $panel->hasPlugin('filament-developer-gate')) {
            $panel->plugin(FilamentDeveloperGatePlugin::make());
        }

        $panel->pages([
            Browser::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
