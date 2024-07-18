<div>
    @php
        $uuid = \Illuminate\Support\Str::uuid();
        if(!\Illuminate\Support\Facades\File::exists(storage_path('app/public/tmp'))) {
            \Illuminate\Support\Facades\File::makeDirectory(storage_path('app/public/tmp'));
        }
        $fileUrl = \Illuminate\Support\Facades\File::copy($file['path'], storage_path('app/public') .'/tmp/'.$uuid.'.' . str($file['extension']))
    @endphp

    @if(str($file['extension'])->contains([
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
        ]))
        <img src="{{ url('storage/tmp/'.$uuid.'.' . $file['extension']) }}" />
    @elseif(str($file['extension'])->contains([
        "mp4",
        "webm",
        "ogg",
        "avi",
        "mov",
        "flv",
    ]))
        <video controls width="100%" height="800px">
            <source src="{{ url('storage/tmp/'.$uuid.'.' . $file['extension']) }}" type="video/{{ $file['extension'] }}">
        </video>
    @elseif(str($file['extension'])->contains([
            "mp3",
            "wav",
            "ogg",
            "flac",
            "aac",
            "wma",
        ]))
        <audio controls width="100%" height="200px">
            <source src="{{ url('storage/tmp/'.$uuid.'.' . $file['extension']) }}" type="audio/{{ $file['extension'] }}">
        </audio>
    @elseif(str($file['extension'])->contains('svg'))
        <div id="svg-preview">
            {!! \Illuminate\Support\Facades\File::get($file['path']) !!}
        </div>
        <style>
            #svg-preview svg {
                width: 100%;
                height: 100%;
            }
        </style>
    @elseif(str($file['extension'])->contains([
        'csv',
        'xls',
        'xlsx',
        'ods',
        'tsv'
    ]))
        @php
            $data = Excel::toArray(new \TomatoPHP\FilamentBrowser\Excel\FileImport(), $file['path'])[0];
        @endphp
        <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @foreach($data as $item)
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75">
                        @foreach($item as $value)
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-for">
                                <div class="fi-ta-col-wrp">
                                    <button class="flex w-full disabled:pointer-events-none justify-start text-start">
                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                            <div class="flex">
                                                <div class="flex max-w-max">
                                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                        <div class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                            {{ $value }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @elseif(str($file['extension'])->contains('pdf'))
        <div class="w-full h-full">
            <iframe src="{{ url('storage/tmp/'.$uuid.'.pdf') }}" width="100%" height="600px"></iframe>
        </div>
    @endif
</div>


