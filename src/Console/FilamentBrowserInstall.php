<?php

namespace TomatoPHP\FilamentBrowser\Console;

use Illuminate\Console\Command;
use TomatoPHP\ConsoleHelpers\Traits\RunCommand;

class FilamentBrowserInstall extends Command
{
    use RunCommand;

    /**
     * @var string
     */
    protected $name = 'filament-browser:install';

    /**
     * @var string
     */
    protected $description = 'install package and publish assets';

    public function handle(): int
    {
        $this->info('Publish Vendor Assets');
        $this->artisanCommand(['optimize:clear']);
        $this->info('Filament Browser installed successfully.');

        return self::SUCCESS;
    }
}
