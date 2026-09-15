<?php

namespace TomatoPHP\FilamentBrowser\Services;

use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;

/**
 * Confines every file operation of the browser to the configured root folder.
 *
 * Paths coming from the client are always relative to the root; they are
 * resolved with realpath() and rejected when they escape the root, point to
 * a hidden file/folder, or contain traversal segments.
 */
class FileBrowser
{
    /**
     * @param  array<int, string>  $hiddenFiles
     * @param  array<int, string>  $hiddenFolders
     * @param  array<int, string>  $hiddenExtensions
     */
    public function __construct(
        protected string $basePath,
        protected array $hiddenFiles = [],
        protected array $hiddenFolders = [],
        protected array $hiddenExtensions = [],
        protected bool $hideEnvFiles = true,
    ) {}

    public static function make(): self
    {
        $plugin = static::plugin();

        return new self(
            basePath: ($plugin?->basePath ?: config('filament-browser.start_path')) ?: base_path(),
            hiddenFiles: $plugin->hiddenFiles ?? [],
            hiddenFolders: $plugin->hiddenFolders ?? [],
            hiddenExtensions: $plugin->hiddenExtensions ?? [],
            hideEnvFiles: $plugin->hideEnvFiles ?? true,
        );
    }

    public static function plugin(): ?FilamentBrowserPlugin
    {
        $panel = filament()->getCurrentOrDefaultPanel();

        if (! $panel?->hasPlugin('filament-browser')) {
            return null;
        }

        /** @var FilamentBrowserPlugin $plugin */
        $plugin = $panel->getPlugin('filament-browser');

        return $plugin;
    }

    public function root(): string
    {
        $root = realpath($this->basePath);

        if ($root === false || ! is_dir($root)) {
            throw new \RuntimeException("Filament Browser base path [{$this->basePath}] does not exist.");
        }

        return $root;
    }

    /**
     * Resolve a client supplied relative path to an existing absolute path inside the root.
     */
    public function resolve(?string $relative): ?string
    {
        $segments = $this->segments($relative);

        if ($segments === null) {
            return null;
        }

        if ($segments === []) {
            return $this->root();
        }

        $real = realpath($this->root().DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $segments));

        if ($real === false || ! $this->isInside($real) || $this->isHidden($real)) {
            return null;
        }

        return $real;
    }

    public function resolveDirectory(?string $relative): ?string
    {
        $path = $this->resolve($relative);

        return ($path !== null && is_dir($path)) ? $path : null;
    }

    public function resolveFile(?string $relative): ?string
    {
        $path = $this->resolve($relative);

        return ($path !== null && is_file($path)) ? $path : null;
    }

    /**
     * Build the absolute path of a new entry called $name inside the relative directory.
     */
    public function target(?string $relativeDirectory, ?string $name): ?string
    {
        $directory = $this->resolveDirectory($relativeDirectory);

        if ($directory === null || ! $this->isValidName($name)) {
            return null;
        }

        $path = $directory.DIRECTORY_SEPARATOR.$name;

        return $this->isHidden($path) ? null : $path;
    }

    public function isValidName(?string $name): bool
    {
        if ($name === null || trim($name) === '' || in_array($name, ['.', '..'], true)) {
            return false;
        }

        return ! preg_match('/[\\\\\/:*?"<>|\x00-\x1F]/', $name);
    }

    public function relative(string $absolute): string
    {
        $root = $this->root();

        if ($this->normalize($absolute) === $this->normalize($root)) {
            return '';
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolute, strlen(rtrim($root, DIRECTORY_SEPARATOR)) + 1));
    }

    public function parent(string $relative): string
    {
        $parent = str_replace('\\', '/', dirname(str_replace('\\', '/', $relative)));

        return in_array($parent, ['.', '/'], true) ? '' : $parent;
    }

    public function isInside(string $absolute): bool
    {
        $root = $this->normalize($this->root());
        $path = $this->normalize($absolute);

        return $path === $root || str_starts_with($path, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
    }

    public function isHidden(string $absolute): bool
    {
        $name = basename($absolute);

        if ($this->hideEnvFiles && ($name === '.env' || str_starts_with($name, '.env.'))) {
            return true;
        }

        $path = $this->normalize($absolute);

        if (! is_dir($absolute)) {
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension !== '' && in_array($extension, array_map('strtolower', $this->hiddenExtensions), true)) {
                return true;
            }
        }

        foreach ($this->hiddenFiles as $hiddenFile) {
            if ($path === $this->normalize($hiddenFile)) {
                return true;
            }
        }

        foreach ($this->hiddenFolders as $hiddenFolder) {
            $folder = rtrim($this->normalize($hiddenFolder), DIRECTORY_SEPARATOR);

            if ($path === $folder || str_starts_with($path, $folder.DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, array{name: string, path: string, type: string, size: string, extension: string}>
     */
    public function list(?string $relative = ''): array
    {
        $directory = $this->resolveDirectory($relative);

        if ($directory === null) {
            return [];
        }

        $rows = [];

        foreach (File::directories($directory) as $folder) {
            $real = realpath($folder);

            if ($real === false || ! $this->isInside($real) || $this->isHidden($real)) {
                continue;
            }

            $path = $this->relative($real);

            $rows[$path] = [
                'name' => basename($real),
                'path' => $path,
                'type' => 'folder',
                'size' => '0',
                'extension' => 'folder',
            ];
        }

        foreach (File::files($directory, true) as $file) {
            $real = $file->getRealPath();

            if ($real === false || ! $this->isInside($real) || $this->isHidden($real)) {
                continue;
            }

            $path = $this->relative($real);

            $rows[$path] = [
                'name' => $file->getFilename(),
                'path' => $path,
                'type' => 'file',
                'size' => static::humanSize((int) $file->getSize()),
                'extension' => $file->getExtension(),
            ];
        }

        return $rows;
    }

    public static function humanSize(int $bytes): string
    {
        return match (true) {
            $bytes < 1000 => $bytes.'bytes',
            $bytes < 1000000 => round($bytes / 1000, 2).'KB',
            $bytes < 1000000000 => round($bytes / 1000000, 2).'MB',
            default => round($bytes / 1000000000, 2).'GB',
        };
    }

    /**
     * @return array<int, string>|null
     */
    protected function segments(?string $relative): ?array
    {
        $relative = str_replace('\\', '/', trim((string) $relative));

        if (str_contains($relative, "\0")) {
            return null;
        }

        if ($relative === '' || $relative === '.' || $relative === '/') {
            return [];
        }

        if (str_starts_with($relative, '/') || preg_match('/^[a-zA-Z]:/', $relative)) {
            return null;
        }

        $segments = explode('/', rtrim($relative, '/'));

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, ':')) {
                return null;
            }
        }

        return $segments;
    }

    protected function normalize(string $path): string
    {
        $real = realpath($path);
        $path = $real !== false ? $real : str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        return PHP_OS_FAMILY === 'Windows' ? strtolower($path) : $path;
    }
}
