<?php

namespace TomatoPHP\FilamentBrowser\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use TomatoPHP\FilamentBrowser\Events\BrowserFileSaved;
use TomatoPHP\FilamentBrowser\Excel\FileImport;
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;
use TomatoPHP\FilamentBrowser\Services\FileBrowser;
use TomatoPHP\FilamentDeveloperGate\Actions\DeveloperLogoutAction;
use TomatoPHP\FilamentDeveloperGate\Http\Middleware\DeveloperGateMiddleware;

class Browser extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * Extensions opened in the code editor.
     */
    public const TEXT_EXTENSIONS = [
        'php', 'json', 'js', 'ts', 'vue', 'css', 'sass', 'scss', 'yaml', 'yml', 'xml', 'lock', 'txt',
        'html', 'htm', 'log', 'env', 'ini', 'sql', 'py', 'go', 'java', 'c', 'cpp', 'cxx', 'sh', 'neon', 'dist',
    ];

    public const PREVIEW_MAX_BYTES = 10_000_000;

    public const EDITOR_MAX_BYTES = 2_000_000;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected string $view = 'filament-browser::browser';

    public static function getNavigationLabel(): string
    {
        return trans('filament-browser::messages.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-browser::messages.group');
    }

    public function getTitle(): string
    {
        return trans('filament-browser::messages.title');
    }

    public function getSubheading(): ?string
    {
        return '/'.$this->getCurrentPath();
    }

    public static function getRouteMiddleware(Panel $panel): string|array
    {
        $plugin = $panel->hasPlugin('filament-browser') ? $panel->getPlugin('filament-browser') : null;

        return ($plugin instanceof FilamentBrowserPlugin && $plugin->useDeveloperGate)
            ? [DeveloperGateMiddleware::class]
            : [];
    }

    public static function canAccess(): bool
    {
        return static::plugin()?->isAuthorized() ?? false;
    }

    /**
     * Runs on every Livewire request, so locked sessions cannot call actions directly.
     */
    public function boot(): void
    {
        abort_unless(static::canAccess(), 403);

        if (static::plugin()?->useDeveloperGate && (session('developer_password') !== config('filament-developer-gate.password'))) {
            abort(403);
        }
    }

    public static function plugin(): ?FilamentBrowserPlugin
    {
        return FileBrowser::plugin();
    }

    protected function browser(): FileBrowser
    {
        return FileBrowser::make();
    }

    public function getCurrentPath(): string
    {
        $path = (string) session('filament-browser-path', '');

        $directory = $this->browser()->resolveDirectory($path);

        if ($directory === null) {
            session()->forget('filament-browser-path');

            return '';
        }

        return $this->browser()->relative($directory);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): array {
                $search = strtolower((string) $this->getTableSearch());

                return collect($this->browser()->list($this->getCurrentPath()))
                    ->when($search !== '', fn ($rows) => $rows->filter(fn (array $row): bool => str_contains(strtolower($row['name']), $search)))
                    ->all();
            })
            ->columns([
                TextColumn::make('name')
                    ->label(trans('filament-browser::messages.files.columns.name'))
                    ->searchable(),
            ])
            ->content(fn () => view('filament-browser::table.content'))
            ->paginated(false);
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            $this->createAction(),
            Action::make('home')
                ->label(trans('filament-browser::messages.actions.home'))
                ->icon('heroicon-o-home')
                ->color('info')
                ->action(function (): void {
                    session()->forget('filament-browser-path');
                    $this->resetTable();
                }),
            Action::make('back')
                ->label(trans('filament-browser::messages.actions.back'))
                ->icon('heroicon-o-chevron-left')
                ->color('warning')
                ->hidden(fn (): bool => $this->getCurrentPath() === '')
                ->action(function (): void {
                    session()->put('filament-browser-path', $this->browser()->parent($this->getCurrentPath()));
                    $this->resetTable();
                }),
        ];

        if (static::plugin()?->useDeveloperGate) {
            $actions[] = DeveloperLogoutAction::make();
        }

        return $actions;
    }

    /**
     * @return array<string, string>
     */
    public function getAllowedCreateTypes(): array
    {
        $plugin = static::plugin();
        $types = [];

        if ($plugin?->allowCreateNewFile) {
            $types['file-code'] = trans('filament-browser::messages.types.code');
            $types['file-markdown'] = trans('filament-browser::messages.types.markdown');
        }

        if ($plugin?->allowCreateFolder) {
            $types['folder'] = trans('filament-browser::messages.types.folder');
        }

        if ($plugin?->allowUpload) {
            $types['upload'] = trans('filament-browser::messages.types.upload');
        }

        return $types;
    }

    protected function createAction(): Action
    {
        return Action::make('create')
            ->label(trans('filament-browser::messages.actions.create'))
            ->icon('heroicon-o-plus')
            ->visible(fn (): bool => $this->getAllowedCreateTypes() !== [])
            ->schema(fn (): array => [
                Select::make('type')
                    ->label(trans('filament-browser::messages.create.type'))
                    ->columnSpanFull()
                    ->required()
                    ->options($this->getAllowedCreateTypes())
                    ->default(array_key_first($this->getAllowedCreateTypes()))
                    ->live(),
                TextInput::make('name')
                    ->label(trans('filament-browser::messages.create.name'))
                    ->required()
                    ->hidden(fn (Get $get): bool => $get('type') === 'upload'),
                Select::make('extension')
                    ->label(trans('filament-browser::messages.create.extension'))
                    ->required()
                    ->searchable()
                    ->options([
                        'php' => 'PHP',
                        'css' => 'CSS',
                        'sass' => 'SASS',
                        'json' => 'JSON',
                        'js' => 'JS',
                        'ts' => 'TS',
                        'vue' => 'Vue',
                        'env' => 'ENV',
                        'yaml' => 'YAML',
                        'xml' => 'XML',
                        'txt' => 'TXT',
                        'html' => 'HTML',
                        'htm' => 'HTM',
                        'blade' => 'BLADE',
                        'log' => 'LOG',
                        'md' => 'MD',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        if (($get('extension') === 'php') && blank($get('code'))) {
                            $set('code', '<?php');
                        }
                    })
                    ->hidden(fn (Get $get): bool => $get('type') !== 'file-code'),
                FileUpload::make('file')
                    ->label(trans('filament-browser::messages.create.file'))
                    ->columnSpanFull()
                    ->required()
                    ->storeFiles(false)
                    ->hidden(fn (Get $get): bool => $get('type') !== 'upload'),
                CodeEditor::make('code')
                    ->label(trans('filament-browser::messages.create.code'))
                    ->columnSpanFull()
                    ->required()
                    ->language(fn (Get $get): ?Language => static::languageFor((string) $get('extension')))
                    ->hidden(fn (Get $get): bool => $get('type') !== 'file-code'),
                MarkdownEditor::make('markdown')
                    ->label(trans('filament-browser::messages.create.markdown'))
                    ->columnSpanFull()
                    ->required()
                    ->hidden(fn (Get $get): bool => $get('type') !== 'file-markdown'),
            ])
            ->action(function (array $data): void {
                $this->createEntry($data);
                $this->resetTable();
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createEntry(array $data): void
    {
        $type = (string) ($data['type'] ?? '');

        if (! array_key_exists($type, $this->getAllowedCreateTypes())) {
            $this->notifyRefused();

            return;
        }

        $directory = $this->getCurrentPath();
        $browser = $this->browser();

        if ($type === 'upload') {
            $file = $data['file'] ?? null;
            $file = is_array($file) ? reset($file) : $file;

            if (! $file instanceof TemporaryUploadedFile) {
                $this->notifyRefused();

                return;
            }

            $target = $browser->target($directory, $file->getClientOriginalName());

            if ($target === null) {
                $this->notifyRefused();

                return;
            }

            if (File::exists($target)) {
                $this->notify('file-exists', danger: true);

                return;
            }

            File::copy($file->getRealPath(), $target);
            $file->delete();
            BrowserFileSaved::dispatch($target);
            $this->notify('uploaded');

            return;
        }

        $name = (string) ($data['name'] ?? '');

        $fileName = match ($type) {
            'file-code' => $name.'.'.($data['extension'] ?? 'txt'),
            'file-markdown' => $name.'.md',
            default => $name,
        };

        $target = $browser->target($directory, $fileName);

        if ($target === null) {
            $this->notifyRefused();

            return;
        }

        if (File::exists($target)) {
            $this->notify($type === 'folder' ? 'folder-exists' : 'file-exists', danger: true);

            return;
        }

        if ($type === 'folder') {
            File::makeDirectory($target);
            $this->notify('created');

            return;
        }

        File::put($target, (string) ($type === 'file-code' ? ($data['code'] ?? '') : ($data['markdown'] ?? '')));
        BrowserFileSaved::dispatch($target);
        $this->notify('saved');
    }

    public function openFolderAction(): Action
    {
        return Action::make('openFolder')
            ->action(function (array $arguments): void {
                $directory = $this->browser()->resolveDirectory((string) ($arguments['path'] ?? ''));

                if ($directory === null) {
                    $this->notifyRefused();

                    return;
                }

                session()->put('filament-browser-path', $this->browser()->relative($directory));
                $this->resetTable();
            });
    }

    public function openFileAction(): Action
    {
        return Action::make('openFile')
            ->modalHeading(fn (array $arguments): string => basename((string) ($arguments['path'] ?? '')))
            ->modalWidth('5xl')
            ->mountUsing(function (Action $action, ?Schema $schema, array $arguments): void {
                $path = $this->browser()->resolveFile((string) ($arguments['path'] ?? ''));

                if ($path === null) {
                    $this->notifyRefused();
                    $action->cancel();

                    return;
                }

                $schema?->fill([
                    'content' => $this->isEditable($path) ? File::get($path) : null,
                ]);
            })
            ->schema(function (array $arguments): array {
                $path = $this->browser()->resolveFile((string) ($arguments['path'] ?? ''));

                if ($path === null) {
                    return [];
                }

                $canEdit = (bool) static::plugin()?->allowEditFile;

                if ($this->isEditable($path)) {
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                    return [
                        $extension === 'md'
                            ? MarkdownEditor::make('content')
                                ->label(trans('filament-browser::messages.edit.content'))
                                ->disabled(! $canEdit)
                            : CodeEditor::make('content')
                                ->label(trans('filament-browser::messages.edit.content'))
                                ->language(static::languageFor($extension))
                                ->disabled(! $canEdit),
                    ];
                }

                if (static::plugin()?->allowPreview) {
                    return [
                        View::make('filament-browser::preview.file')
                            ->viewData(['preview' => $this->buildPreview($path)]),
                    ];
                }

                return [];
            })
            ->modalSubmitAction(function (Action $action, array $arguments): Action|false {
                $path = $this->browser()->resolveFile((string) ($arguments['path'] ?? ''));

                return ($path !== null && static::plugin()?->allowEditFile && $this->isEditable($path))
                    ? $action->label(trans('filament-browser::messages.save'))
                    : false;
            })
            ->extraModalFooterActions(fn (array $arguments): array => [
                Action::make('renameFile')
                    ->label(trans('filament-browser::messages.actions.rename'))
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (): bool => (bool) static::plugin()?->allowRenameFile)
                    ->cancelParentActions()
                    ->schema([
                        TextInput::make('name')
                            ->label(trans('filament-browser::messages.files.columns.name'))
                            ->required(),
                    ])
                    ->fillForm(['name' => basename((string) ($arguments['path'] ?? ''))])
                    ->action(function (array $data) use ($arguments): void {
                        $this->renameFile((string) ($arguments['path'] ?? ''), (string) ($data['name'] ?? ''));
                    }),
                Action::make('deleteFile')
                    ->label(trans('filament-browser::messages.actions.delete'))
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (): bool => (bool) static::plugin()?->allowDeleteFile)
                    ->requiresConfirmation()
                    ->cancelParentActions()
                    ->action(function () use ($arguments): void {
                        $this->deleteFile((string) ($arguments['path'] ?? ''));
                    }),
            ])
            ->action(function (array $arguments, array $data): void {
                $this->saveFile((string) ($arguments['path'] ?? ''), $data);
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function saveFile(string $relative, array $data): void
    {
        $path = $this->browser()->resolveFile($relative);

        if (! static::plugin()?->allowEditFile || ($path === null) || ! $this->isEditable($path)) {
            $this->notifyRefused();

            return;
        }

        if (! array_key_exists('content', $data)) {
            return;
        }

        File::put($path, (string) $data['content']);
        BrowserFileSaved::dispatch($path);
        $this->notify('saved');
    }

    protected function renameFile(string $relative, string $name): void
    {
        $browser = $this->browser();
        $path = $browser->resolveFile($relative);

        if (! static::plugin()?->allowRenameFile || ($path === null)) {
            $this->notifyRefused();

            return;
        }

        $target = $browser->target($browser->parent($browser->relative($path)), $name);

        if ($target === null) {
            $this->notifyRefused();

            return;
        }

        if (File::exists($target)) {
            $this->notify('file-exists', danger: true);

            return;
        }

        File::move($path, $target);
        $this->notify('renamed');
        $this->resetTable();
    }

    protected function deleteFile(string $relative): void
    {
        $path = $this->browser()->resolveFile($relative);

        if (! static::plugin()?->allowDeleteFile || ($path === null)) {
            $this->notifyRefused();

            return;
        }

        File::delete($path);
        $this->notify('deleted');
        $this->resetTable();
    }

    public function isEditable(string $path): bool
    {
        if (filesize($path) > static::EDITOR_MAX_BYTES) {
            return false;
        }

        $name = basename($path);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return ($extension === '') || ($extension === 'md') || str_starts_with($name, '.') || in_array($extension, static::TEXT_EXTENSIONS, true);
    }

    /**
     * @return array{kind: string, src?: string, rows?: array<int, array<int, mixed>>}
     */
    public function buildPreview(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $kind = match (true) {
            in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'bmp'], true) => 'image',
            in_array($extension, ['mp4', 'webm', 'mov', 'ogv'], true) => 'video',
            in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'], true) => 'audio',
            $extension === 'pdf' => 'pdf',
            in_array($extension, ['csv', 'tsv', 'xls', 'xlsx', 'ods'], true) => 'sheet',
            default => 'none',
        };

        if ($kind === 'none') {
            return ['kind' => 'none'];
        }

        if (filesize($path) > static::PREVIEW_MAX_BYTES) {
            return ['kind' => 'too-large'];
        }

        if ($kind === 'sheet') {
            try {
                $rows = Excel::toArray(new FileImport, $path)[0] ?? [];
            } catch (Throwable) {
                $rows = [];
            }

            return ['kind' => 'sheet', 'rows' => array_slice($rows, 0, 200)];
        }

        $mime = $extension === 'svg' ? 'image/svg+xml' : (File::mimeType($path) ?: 'application/octet-stream');

        return ['kind' => $kind, 'src' => 'data:'.$mime.';base64,'.base64_encode(File::get($path))];
    }

    public static function languageFor(?string $extension): ?Language
    {
        return match (strtolower((string) $extension)) {
            'c', 'cpp', 'cxx' => Language::Cpp,
            'css', 'sass', 'scss' => Language::Css,
            'go' => Language::Go,
            'html', 'htm', 'blade' => Language::Html,
            'java' => Language::Java,
            'js', 'ts', 'vue' => Language::JavaScript,
            'json', 'lock' => Language::Json,
            'md' => Language::Markdown,
            'php' => Language::Php,
            'py' => Language::Python,
            'sql' => Language::Sql,
            'xml' => Language::Xml,
            'yaml', 'yml', 'neon' => Language::Yaml,
            default => null,
        };
    }

    /**
     * @param  array{name: string, extension: string, type: string}  $item
     * @return array{0: string, 1: string}
     */
    public static function iconFor(array $item): array
    {
        if ($item['type'] === 'folder') {
            return ['bxs-folder', '#edbd0e'];
        }

        $name = strtolower($item['name']);
        $extension = strtolower($item['extension']);

        return match (true) {
            str_starts_with($name, '.env') => ['bxs-cog', '#ecd53e'],
            in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'tif', 'ico', 'bmp'], true) => ['bxs-file-image', '#1451e0'],
            in_array($extension, ['mp4', 'webm', 'ogv', 'avi', 'mov', 'flv'], true) => ['bxs-video', '#e82a2a'],
            in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma', 'm4a'], true) => ['bxs-music', '#e82ad5'],
            in_array($extension, ['csv', 'xls', 'xlsx', 'ods', 'tsv'], true) => ['bxs-spreadsheet', '#55d415'],
            $extension === 'pdf' => ['bxs-file-pdf', '#6722d6'],
            $extension === 'json' => ['bxs-file-json', '#6722d6'],
            $extension === 'md' => ['bxs-file-md', '#6722d6'],
            str_contains($name, 'tailwind') => ['bxl-tailwind-css', '#38bdf8'],
            $extension === 'js' => ['bxl-javascript', '#f0db4f'],
            $extension === 'lock' => ['bxl-nodejs', '#68a063'],
            str_starts_with($name, '.git') => ['bxl-github', '#3e75c4'],
            in_array($extension, ['html', 'htm'], true) || str_ends_with($name, '.blade.php') => ['bxl-html5', '#e34c26'],
            $extension === 'css' => ['bxl-css3', '#264de4'],
            $extension === 'php' => ['bxl-php', '#8993be'],
            default => ['bxs-file-blank', '#22b8d6'],
        };
    }

    protected function notify(string $key, bool $danger = false): void
    {
        $notification = Notification::make()->title(trans("filament-browser::messages.notifications.{$key}"));

        ($danger ? $notification->danger() : $notification->success())->send();
    }

    protected function notifyRefused(): void
    {
        $this->notify('refused', danger: true);
    }
}
