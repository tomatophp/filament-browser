<?php

namespace TomatoPHP\FilamentBrowser\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched after the browser writes a file (create, edit or upload).
 */
class BrowserFileSaved
{
    use Dispatchable;

    public function __construct(
        public string $filename
    ) {}
}
