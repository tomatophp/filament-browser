![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/3x1io-tomato-browser.jpg)

# Filament File Browser

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-browser/version.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![License](https://poser.pugx.org/tomatophp/filament-browser/license.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![Downloads](https://poser.pugx.org/tomatophp/filament-browser/d/total.svg)](https://packagist.org/packages/tomatophp/filament-browser)

File & Folders & Media Browser With Code Editor

> [!CAUTION]
> This package is for super-admin only and it's not recommended to use it for normal users. because it's give access to all files and folders in your server.

## Screenshots

![Browser](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser.png)
![File Types](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/file-types.png)
![Create File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/create-file.png)
![Delete File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/delete.png)
![Rename File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/rename.png)
![Markdown Editor](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/markdown.png)
![Code Editor](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/code.png)
![Video File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/video.png)
![Audio File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/audio.png)
![Excel File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/excel.png)
![Image File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/image.png)
![PDF File](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/pdf.png)

## Installation

```bash
composer require tomatophp/filament-browser
```
after install your package please run this command

```bash
php artisan filament-browser:install
```

finally reigster the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(
    \TomatoPHP\FilamentBrowser\FilamentBrowserPlugin::make()
        ->hiddenFolders([
            base_path('app')
        ])
        ->hiddenFiles([
            base_path('.env')
        ])
        ->hiddenExtensions([
            "php"
        ])
        ->allowCreateFolder()
        ->allowEditFile()
        ->allowCreateNewFile()
        ->allowCreateFolder()
        ->allowRenameFile()
        ->allowDeleteFile()
        ->allowMarkdown()
        ->allowCode()
        ->allowPreview()
        ->basePath(base_path())
)
```

when you try to access the browser it will ask you about password it's `password` and you can change it from your `.env` file

```env
DEVELOPER_GATE_PASSWORD=
```

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

you can publish migrations file by use this command

```bash
php artisan vendor:publish --tag="filament-browser-migrations"
```

## Other Filament Packages

Checkout our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)
