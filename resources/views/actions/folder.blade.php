<x-filament-actions::action
    :action="$action"
    :badge="$getBadge()"
    :badge-color="$getBadgeColor()"
    color="null"
    dynamic-component="filament::button"
    :label="$getLabel()"
    :size="$getSize()"
    style="box-shadow: none !important;"
>
    <div class="flex flex-col justify-center items-center gap-2">
        <x-bxs-folder class="w-20 h-20" style="color: #edbd0e !important;"/>
        <div class="font-medium text-center" style="color: #edbd0e !important;">
            {{ $file->name }}
        </div>
    </div>
</x-filament-actions::action>
