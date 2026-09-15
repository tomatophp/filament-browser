# Filament Browser v4 Migration Guide

This document outlines the changes made to update the Filament Browser package from Filament v3 to v4.

## Changes Made

### 1. Dependencies Updated
- ✅ Updated `composer.json` to require `filament/filament: ^4.0`
- ✅ Removed dependency on `tomatophp/filament-developer-gate`
- ✅ Removed dependency on `creagia/filament-code-field`

### 2. Code Field Replacement
- ✅ Replaced custom `filament-code-field` component with Filament v4's built-in `CodeEditor`
- ✅ Updated language handling to use `Filament\Forms\Components\CodeEditor\Enums\Language`
- ✅ Removed old custom code component (`resources/views/components/code.blade.php`)

### 3. Developer Gate Removal
- ✅ Removed all references to `filament-developer-gate`
- ✅ Cleaned up header actions in `Browser.php`
- ✅ Removed unused route that referenced `DeveloperGateMiddleware`

### 4. Plugin Registration
- ✅ Updated `FilamentBrowserPlugin` to properly register pages for Filament v4
- ✅ Plugin now correctly registers the `Browser` page

## Usage

To use this package with Filament v4, register it in your `AdminPanelProvider.php`:

```php
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;

// In your panel configuration
->plugin(
    FilamentBrowserPlugin::make()
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

## Features Still Working

- ✅ File and folder browsing
- ✅ Code editing with syntax highlighting (now using Filament v4's CodeEditor)
- ✅ File creation, deletion, renaming
- ✅ Markdown editing
- ✅ File preview (images, videos, audio, PDFs, etc.)
- ✅ Excel file viewing
- ✅ All file type icons and recognition

## Security Note

This package provides access to server files and should only be used by super-administrators. Always configure appropriate `hiddenFiles`, `hiddenFolders`, and `hiddenExtensions` to protect sensitive files.

## Testing

All core classes load successfully with Filament v4. The package is ready for use.