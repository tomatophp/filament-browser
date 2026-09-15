<?php

namespace TomatoPHP\FilamentBrowser\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;
use TomatoPHP\FilamentBrowser\Services\FileBrowser;

/**
 * Read-only listing of the current browser folder (paths are relative to the configured root).
 */
class Files extends Model
{
    use Sushi;

    /**
     * @var array<string, string>
     */
    protected $schema = [
        'name' => 'string',
        'path' => 'string',
        'type' => 'string',
        'size' => 'string',
        'extension' => 'string',
    ];

    /**
     * @return array<int, array{name: string, path: string, type: string, size: string, extension: string}>
     */
    public function getRows(): array
    {
        return array_values(FileBrowser::make()->list((string) session('filament-browser-path', '')));
    }
}
