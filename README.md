![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/3x1io-tomato-browser.jpg)

# Filament File Browser

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-browser/version.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![License](https://poser.pugx.org/tomatophp/filament-browser/license.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![Downloads](https://poser.pugx.org/tomatophp/filament-browser/d/total.svg)](https://packagist.org/packages/tomatophp/filament-browser)

File & Folders & Media Browser With Code Editor

> [!CAUTION]
> This package gives access to files on your server. Use it for super-admins only, keep the developer gate on, and point `basePath()` at the smallest folder you need.

## Screenshots

![Demo dark](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/demo-dark.png)
![Demo light](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/demo-light.png)
![Markdown preview](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/demo-preview.png)

![Browser](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser.png)
![File Types](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/file-types.png)
![Create File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/create.png)
![Delete File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/delete.png)
![Rename File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/rename.png)
![Markdown Editor](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/markdown.png)
![Code Editor](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/code.png)
![Video File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/video.png)
![Audio File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/audio.png)
![Excel File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/excel.png)
![Image File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/image.png)
![PDF File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/pdf.png)

## Version Compatibility

| Plugin | Filament | Laravel | PHP |
|--------|----------|---------|-----|
| 5.x    | 5.x      | 12.x / 13.x | 8.2+ |
| 1.x / 2.x ([v3 branch](https://github.com/tomatophp/filament-browser/tree/v3)) | 3.x | 10.x / 11.x | 8.1+ |

## Installation

```bash
composer require tomatophp/filament-browser
```

after install your package please run this command

```bash
php artisan filament-browser:install
```

finally register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;

->plugin(
    FilamentBrowserPlugin::make()
        ->basePath(storage_path('app/browser'))
        ->hiddenFolders([
            base_path('app'),
        ])
        ->hiddenFiles([
            base_path('composer.lock'),
        ])
        ->hiddenExtensions([
            'php',
        ])
        ->allowCreateFolder()
        ->allowCreateNewFile()
        ->allowUpload()
        ->allowEditFile()
        ->allowRenameFile()
        ->allowDeleteFile()
        ->allowPreview()
)
```

The page is available at `/admin/browser` (under the **Settings** navigation group).

## Options

| Method | Default | Description |
|--------|---------|-------------|
| `basePath(string $path)` | `config('filament-browser.start_path')` (`base_path()`) | Root folder. Every operation is confined to it. |
| `allowCreateNewFile(bool = true)` | `false` | Create code and markdown files. |
| `allowCreateFolder(bool = true)` | `false` | Create folders. |
| `allowUpload(bool = true)` | `false` | Upload files into the current folder. |
| `allowEditFile(bool = true)` | `false` | Save changes from the code / markdown editor (read-only otherwise). |
| `allowRenameFile(bool = true)` | `false` | Rename files. |
| `allowDeleteFile(bool = true)` | `false` | Delete files. |
| `allowPreview(bool = true)` | `true` | Preview images, video, audio, PDF and spreadsheets. |
| `hiddenFiles(array)` / `hiddenFolders(array)` | `[]` | Absolute paths that are not listed and cannot be opened. |
| `hiddenExtensions(array)` | `[]` | Extensions that are not listed, opened or created (e.g. `['php']`). |
| `hideEnvFiles(bool = true)` | `true` | `.env` and `.env.*` are hidden; call `hideEnvFiles(false)` to show them. |
| `developerGate(bool = true)` | `true` | Protect the page with [filament-developer-gate](https://github.com/tomatophp/filament-developer-gate). |
| `authorize(bool\|Closure)` | `true` | Who may open the page, e.g. `fn () => auth()->user()->hasRole('super_admin')`. |

`allowCode()` and `allowMarkdown()` are kept for backward compatibility and have no effect.

## Developer Gate

The browser asks for the developer password before it opens. The default password is `password`; change it in your `.env`:

```env
DEVELOPER_GATE_PASSWORD=
```

## Security

- Every path sent from the browser is relative to `basePath()`, resolved with `realpath()` and refused when it leaves the root (`..`, absolute paths, symlinks pointing outside), or when it is hidden.
- `.env` files are hidden by default, and hidden files, folders and extensions can not be opened, created or renamed to.
- Nothing can be written unless you enable the matching `allow*()` option, and the checks run again on the server when the action runs.
- Previews are embedded in the page; files are never copied to public storage.
- The developer gate is checked on every Livewire request, not only on the page route.
- Do not point `basePath()` at a folder served by your web server (such as `public/`) while `allowCreateNewFile()` or `allowUpload()` is enabled, or hide the `php` extension.

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-browser-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="filament-browser-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="filament-browser-lang"
```

## Testing

```bash
composer test
```

## Other Filament Packages

Checkout our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)
