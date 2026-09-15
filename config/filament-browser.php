<?php

return [
    /*
     * The folder the browser starts in when the plugin does not set basePath().
     * Every file operation is confined to this folder.
     */
    'start_path' => env('FILAMENT_BROWSER_START_PATH', base_path()),
];
