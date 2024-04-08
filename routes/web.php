<?php


use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentBrowser\Http\Controllers\BrowserController;


Route::post('admin/browser/json', [BrowserController::class, 'index'])->middleware('web');
