![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/3x1io-tomato-browser.jpg)

# Filament File Browser

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-browser/version.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![PHP Version Require](http://poser.pugx.org/tomatophp/filament-browser/require/php)](https://packagist.org/packages/tomatophp/filament-browser)
[![License](https://poser.pugx.org/tomatophp/filament-browser/license.svg)](https://packagist.org/packages/tomatophp/filament-browser)
[![Downloads](https://poser.pugx.org/tomatophp/filament-browser/d/total.svg)](https://packagist.org/packages/tomatophp/filament-browser)

File & Folders & Media Browser With Code Editor

> [!CAUTION]
> This package is for super-admin only and it's not recommended to use it for normal users. because it's give access to all files and folders in your server.

## Screenshots

![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser.png)
![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser-editor.png)
![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser-media.png)
![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-browser/master/arts/browser-image.png)

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
->plugin(\TomatoPHP\FilamentBrowser\FilamentBrowserPlugin::make())
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

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/filament/filament-browser)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](https://wa.me/+201207860084)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
