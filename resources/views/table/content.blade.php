<div class="p-4">
    @if($records->isNotEmpty())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($records as $item)
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg flex flex-col items-center p-4">
                    @if($item['type'] === 'folder')
                        {{ ($this->getFolderAction($item))(["file" => $item])  }}
                    @else
                        {{ ($this->getFileAction($item))(["file" => $item]) }}
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <x-filament-tables::empty-state
            heading="{{ trans('filament-browser::messages.empty') }}"
            icon="heroicon-o-x-circle"
        />
    @endif
</div>
