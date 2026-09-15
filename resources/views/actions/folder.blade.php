{{ $action }}
@if($action->isVisible())
    <button wire:click="mountAction('{{ $action->getName() }}', {{ json_encode(['file' => $file]) }})"
        class="flex flex-col justify-center items-center gap-2 p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        style="box-shadow: none !important;">
        <x-bxs-folder class="w-20 h-20" style="color: #edbd0e !important;" />
        <div class="font-medium text-center" style="color: #edbd0e !important;">
            {{ $file->name }}
        </div>
    </button>
@endif