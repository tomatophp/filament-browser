# Changelog

## v5.0.0

- Support Filament v5, Livewire 4 and Laravel 12 / 13 (PHP 8.2+).
- Replace `creagia/filament-code-field` with Filament's built-in `CodeEditor`.
- Every file operation is confined to `basePath()` with `realpath()` checks; traversal, absolute paths and symlinks leaving the root are refused.
- `.env` files are hidden by default (`hideEnvFiles(false)` to show them).
- Create, edit, upload, rename and delete are checked again on the server against the `allow*()` options.
- The developer gate (`tomatophp/filament-developer-gate` ^5.0) is enforced on every Livewire request; new `developerGate()` and `authorize()` options.
- Previews are embedded in the page instead of being copied into public storage.
- Add support for the `htm` extension (#12, thanks @MohammadSalahat).
- Fix an Arabic translation typo (#11, thanks @Abdelrahman842003).
- Remove the unused legacy JSON controller, route and JS bundle.
- Add a Pest test suite.
