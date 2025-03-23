<?php

namespace TomatoPHP\FilamentBrowser\Pages;

use Creagia\FilamentCodeField\CodeField;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use TomatoPHP\FilamentBrowser\Models\Files;
use TomatoPHP\FilamentDeveloperGate\Traits\DeveloperGateLogoutAction;
use TomatoPHP\FilamentDeveloperGate\Traits\InteractWithDeveloperGate;

class Browser extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractWithDeveloperGate;


    public string $language = "php";

    #[On('refreshTable')]
    public function refreshTable()
    {
        $this->dispatch('$refresh');
        $this->resetTableFiltersForm();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('filament-browser::messages.files.columns.name'))
                    ->searchable()
                    ->sortable()
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('create')
                    ->hidden(fn() => !filament('filament-browser')->allowCreateNewFile)
                    ->label(trans('filament-browser::messages.actions.create'))
                    ->icon('heroicon-o-plus')
                    ->form(function () {
                        $type = [
                            'file-code' => trans('filament-browser::messages.types.code'),
                            'file-markdown' => trans('filament-browser::messages.types.markdown'),
                        ];
                        if (filament('filament-browser')->allowCreateFolder) {
                            $type['folder'] = trans('filament-browser::messages.types.folder');
                        }
                        if (filament('filament-browser')->allowUpload) {
                            $type['upload'] = trans('filament-browser::messages.types.upload');
                        }
                        return [
                            Select::make('type')
                                ->label(trans('filament-browser::messages.create.type'))
                                ->columnSpanFull()
                                ->required()
                                ->default('file-code')
                                ->searchable()
                                ->options([
                                    'upload' => trans('filament-browser::messages.types.upload'),
                                    'file-code' => trans('filament-browser::messages.types.code'),
                                    'file-markdown' => trans('filament-browser::messages.types.markdown'),
                                    'folder' => trans('filament-browser::messages.types.folder'),
                                ])
                                ->live(),
                            TextInput::make('name')
                                ->label(trans('filament-browser::messages.create.name'))
                                ->required()
                                ->hidden(fn(Get $get) => $get('type') == 'upload'),
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
                                    'blade' => 'BLADE',
                                    'log' => 'LOG',
                                    'md' => 'MD',
                                ])
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $this->language = $get('extension');
                                    if ($get('extension') === 'php') {
                                        $set('code', '<?php');
                                    }
                                })
                                ->hidden(fn(Get $get) => $get('type') != 'file-code'),
                            FileUpload::make('file')
                                ->label(trans('filament-browser::messages.create.file'))
                                ->columnSpanFull()
                                ->required()
                                ->preserveFilenames()
                                ->hidden(fn(Get $get) => $get('type') != 'upload'),
                            CodeField::make('code')
                                ->label(trans('filament-browser::messages.create.code'))
                                ->columnSpanFull()
                                ->required()
                                ->view('filament-browser::components.code')
                                ->setLanguage($this->language ?? 'php')
                                ->hidden(fn(Get $get) => $get('type') != 'file-code'),
                            MarkdownEditor::make('markdown')
                                ->label(trans('filament-browser::messages.create.markdown'))
                                ->columnSpanFull()
                                ->required()
                                ->hidden(fn(Get $get) => $get('type') != 'file-markdown'),
                        ];
                    })
                    ->action(function (array $data) {
                        $path = session()->has('filament-browser-path') ? session()->get('filament-browser-path') : (filament('filament-browser')->basePath ?: config('filament-browser.start_path'));
                        if ($data['type'] === 'upload') {
                            $exists = File::exists($path . '/' . $data['file']);
                            if ($exists) {
                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                File::copy(storage_path('app/public/' . $data['file']), $path . '/' . $data['file']);

                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.uploaded'))
                                    ->success()
                                    ->send();
                            }

                            File::delete(storage_path('app/public/' . $data['file']));
                        } elseif ($data['type'] === 'file-code') {
                            $exists = File::exists($path . "/{$data['name']}.{$data['extension']}");
                            if ($exists) {
                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();
                                return;
                            }
                            File::put($path . "/{$data['name']}.{$data['extension']}", $data['code']);

                            Notification::make()
                                ->title(trans('filament-browser::messages.notifications.saved'))
                                ->success()
                                ->send();
                        } elseif ($data['type'] === 'file-markdown') {
                            $exists = File::exists($path . "/{$data['name']}.md");
                            if ($exists) {
                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();
                                return;
                            }
                            File::put($path . "/{$data['name']}.md", $data['markdown']);

                            Notification::make()
                                ->title(trans('filament-browser::messages.notifications.saved'))
                                ->success()
                                ->send();
                        } elseif ($data['type'] === 'folder') {
                            $exists = File::exists($path . "/{$data['name']}");
                            if ($exists) {
                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.folder-exists'))
                                    ->danger()
                                    ->send();
                                return;
                            }

                            File::makeDirectory($path . "/{$data['name']}");

                            Notification::make()
                                ->title(trans('filament-browser::messages.notifications.created'))
                                ->success()
                                ->send();
                        }

                        $this->dispatch('refreshTable');
                    }),
                \Filament\Tables\Actions\Action::make('home')
                    ->label(trans('filament-browser::messages.actions.home'))
                    ->icon('heroicon-o-home')
                    ->color('info')
                    ->action(function () {
                        session()->forget('filament-browser-path');
                        session()->forget('filament-browser-current');

                        $this->dispatch('refreshTable');
                    }),
                \Filament\Tables\Actions\Action::make('back')
                    ->label(trans('filament-browser::messages.actions.back'))
                    ->icon('heroicon-o-chevron-left')
                    ->color('warning')
                    ->hidden(fn() => !session()->has('filament-browser-current') || count(json_decode(session()->get('filament-browser-current'))) < 1)
                    ->action(function () {
                        $history = collect(json_decode(session()->get('filament-browser-current')))->last();
                        $historyAfter = collect(json_decode(session()->get('filament-browser-current')))->filter(fn($item) => $item != $history);
                        session()->put('filament-browser-current', json_encode($historyAfter->toArray()));
                        session()->put('filament-browser-path', $historyAfter->last());

                        $this->dispatch('refreshTable');
                    }),
            ])
            ->content(fn() => view('filament-browser::table.content'))
            ->paginated(false)
            ->query(Files::query());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('developer_gate_logout')
                ->action(function () {
                    session()->forget('developer_password');

                    Notification::make()
                        ->title(trans('filament-developer-gate::messages.notifications.logout.title'))
                        ->body(trans('filament-developer-gate::messages.notifications.logout.body'))
                        ->success()
                        ->send();

                    return redirect()->to(config('filament-developer-gate.route_prefix') . '/developer-gate');
                })
                ->requiresConfirmation()
                ->color('danger')
                ->label(trans('filament-developer-gate::messages.logout'))
        ];
    }

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static string $view = 'filament-browser::browser';

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

    public static function shouldRegisterNavigation(): bool
    {
        return !filament('filament-browser')->hideFromPanel;
    }

    public function getFolderAction(?Files $file = null)
    {
        return Action::make('getFolderAction')
            ->view('filament-browser::actions.folder', ['file' => $file])
            ->action(function (array $arguments) {
                $currentArray = session()->has('filament-browser-current') ? json_decode(session()->get('filament-browser-current')) : [];
                $currentArray[] = $arguments['file']['path'];
                session()->put('filament-browser-current', json_encode($currentArray));
                session()->put('filament-browser-path', $arguments['file']['path']);

                $this->dispatch('refreshTable');
            });
    }

    public function getFileAction(?Files $file = null)
    {
        return Action::make('getFileAction')
            ->label(function (array $arguments) {
                return $arguments['file']['name'];
            })
            ->fillForm(function (array $arguments) {
                return [
                    'content' => (str($arguments['file']['extension'])->contains([
                        "php",
                        "json",
                        "js",
                        "yaml",
                        "xml",
                        "lock",
                        "txt",
                        "html",
                        "log",
                        "md",
                    ]) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension'])) ? File::get($arguments['file']['path']) : $arguments['file'],
                ];
            })
            ->form(function (array $arguments) {
                return ((str($arguments['file']['extension'])->contains([
                    "php",
                    "json",
                    "js",
                    "yaml",
                    "xml",
                    "lock",
                    "txt",
                    "html",
                    "log",
                ]) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension'])) ? [
                    CodeField::make('content')
                        ->disabled(fn() => !filament('filament-browser')->allowEditFile)
                        ->label(trans('filament-browser::messages.edit.content'))
                        ->view('filament-browser::components.code')
                        ->setLanguage($arguments['file']['extension']),
                ] : (str($arguments['file']['extension'])->contains('md') ? [MarkdownEditor::make('content')->label(trans('filament-browser::messages.edit.content'))->disabled(fn() => !filament('filament-browser')->allowEditFile)] : []));
            })
            ->extraModalFooterActions(function (array $arguments, Action $action) {
                return [
                    Action::make('deleteFile')
                        ->hidden(fn() => !filament('filament-browser')->allowDeleteFile)
                        ->label(trans('filament-browser::messages.actions.delete'))
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->cancelParentActions()
                        ->action(function () use ($arguments, $action) {
                            File::delete($arguments['file']['path']);

                            Notification::make()
                                ->title(trans('filament-browser::messages.notifications.deleted'))
                                ->success()
                                ->send();

                            $this->dispatch('refreshTable');
                        }),
                    Action::make('rename_file')
                        ->hidden(fn() => !filament('filament-browser')->allowRenameFile)
                        ->label(trans('filament-browser::messages.actions.rename'))
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square')
                        ->requiresConfirmation()
                        ->cancelParentActions()
                        ->form([
                            TextInput::make('name')
                                ->label(trans('filament-browser::messages.files.columns.name'))
                        ])
                        ->fillForm([
                            "name" => $arguments['file']['name']
                        ])
                        ->action(function (array $data) use ($arguments, $action) {
                            $exists = File::exists($arguments['file']['path']);
                            if ($exists) {
                                File::move($arguments['file']['path'], str_replace($arguments['file']['name'], $data['name'], $arguments['file']['path']));

                                Notification::make()
                                    ->title(trans('filament-browser::messages.notifications.renamed'))
                                    ->success()
                                    ->send();
                            }

                            $this->dispatch('refreshTable');
                        }),
                ];
            })
            ->infolist(function (array $arguments) {
                File::deleteDirectory(storage_path('app/public/tmp'));
                return filament('filament-browser')->allowPreview ? ((str($arguments['file']['extension'])->contains([
                    "php",
                    "json",
                    "js",
                    "yaml",
                    "xml",
                    "lock",
                    "txt",
                    "html",
                    "log",
                    "md",
                ])) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension']) ? [] : [
                    TextEntry::make('content')
                        ->hidden(fn() => !filament('filament-browser')->allowPreview)
                        ->label(trans('filament-browser::messages.edit.content'))
                        ->view('filament-browser::preview.file', ['file' => $arguments['file']])
                ]) : [];
            })
            ->action(function (array $arguments, array $data) {
                if (filament('filament-browser')->allowEditFile) {
                    (str($arguments['file']['extension'])->contains([
                        "php",
                        "json",
                        "js",
                        "yaml",
                        "xml",
                        "lock",
                        "txt",
                        "html",
                        "log",
                        "md",
                    ])) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension']) ? File::put($arguments['file']['path'], $data['content']) : null;

                    if (isset($data) && isset($data['content'])) {
                        Notification::make()
                            ->title(trans('filament-browser::messages.notifications.saved'))
                            ->success()
                            ->send();
                    }
                }
            })
            ->view('filament-browser::actions.file', ['file' => $file]);
    }
}
