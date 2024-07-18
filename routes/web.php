<?php


use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentBrowser\Http\Controllers\BrowserController;
use TomatoPHP\FilamentDeveloperGate\Http\Middleware\DeveloperGateMiddleware;


//Route::post('admin/browser/json', [BrowserController::class, 'index'])->middleware(['web', DeveloperGateMiddleware::class]);
Route::get('browser/file', [BrowserController::class, 'file'])->middleware(['web', DeveloperGateMiddleware::class, 'auth:web']);
