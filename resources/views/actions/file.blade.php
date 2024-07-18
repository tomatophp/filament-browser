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
        @if(str($file->name)->contains([
        'env',
        ]))
            <x-bxs-cog class="w-20 h-20" style="color: #ecd53e !important;" />
            <div class="font-medium text-center" style="color: #ecd53e !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            "png",
            "jpg",
            "jpeg",
            "gif",
            "webp",
            "svg",
            "tif"
        ]))
            <x-bxs-file-image class="w-20 h-20" style="color: #1451e0 !important;"/>
            <div class="font-medium text-center" style="color: #1451e0 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            "mp4",
            "webm",
            "ogg",
            "avi",
            "mov",
            "flv",
        ]))
            <x-bxs-video class="w-20 h-20" style="color: #e82a2a !important;"/>
            <div class="font-medium text-center" style="color: #e82a2a !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            "mp3",
            "wav",
            "ogg",
            "flac",
            "aac",
            "wma",
        ]))
            <x-bxs-music class="w-20 h-20" style="color: #e82ad5 !important;"/>
            <div class="font-medium text-center" style="color: #e82ad5 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'csv',
            'xls',
            'xlsx',
            'ods',
            'tsv'
        ]))
            <x-bxs-spreadsheet class="w-20 h-20" style="color: #55d415 !important;" />
            <div class="font-medium text-center" style="color: #55d415 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'pdf',
        ]))
            <x-bxs-file-pdf class="w-20 h-20" style="color: #6722d6 !important;" />
            <div class="font-medium text-center" style="color: #6722d6 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'json',
        ]))
            <x-bxs-file-json class="w-20 h-20" style="color: #6722d6 !important;" />
            <div class="font-medium text-center" style="color: #6722d6 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'md',
        ]))
            <x-bxs-file-md class="w-20 h-20" style="color: #6722d6 !important;" />
            <div class="font-medium text-center" style="color: #6722d6 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->name)->contains([
            'tailwind',
        ]))
            <x-bxl-tailwind-css class="w-20 h-20" style="color: #a5f3fc !important;" />
            <div class="font-medium text-center" style="color: #a5f3fc !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'js',
        ]))
            <x-bxl-javascript class="w-20 h-20" style="color: #f0db4f !important;" />
            <div class="font-medium text-center" style="color: #f0db4f !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
           'lock',
       ]))
            <x-bxl-nodejs class="w-20 h-20" style="color: #68a063 !important;" />
            <div class="font-medium text-center" style="color: #68a063 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->name)->contains([
        'git',
        ]))
            <x-bxl-github class="w-20 h-20" style="color: #3e75c4 !important;" />
            <div class="font-medium text-center" style="color: #3e75c4 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'html'
        ]) || str($file->name)->contains([
            'blade'
        ]))
            <x-bxl-html5 class="w-20 h-20" style="color: #e34c26 !important;" />
            <div class="font-medium text-center" style="color: #e34c26 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'css',
        ]))
            <x-bxl-css3 class="w-20 h-20" style="color: #264de4 !important;" />
            <div class="font-medium text-center" style="color: #264de4 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @elseif(str($file->extension)->contains([
            'php',
        ]))
            <x-bxl-php class="w-20 h-20" style="color: #8993be !important;" />
            <div class="font-medium text-center" style="color: #8993be !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @else
            <x-bxs-file-blank v-else class="w-20 h-20" style="color: #22b8d6 !important;"/>
            <div class="font-medium text-center" style="color: #22b8d6 !important;">
                {{ $file->name }} [{{ $file->size }}]
            </div>
        @endif
    </div>
</x-filament-actions::action>
