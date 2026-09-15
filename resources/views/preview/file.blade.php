<div>
    @switch($preview['kind'])
        @case('image')
            <img src="{{ $preview['src'] }}" alt="" style="max-width: 100%; margin: 0 auto;" />
            @break
        @case('video')
            <video controls style="width: 100%;">
                <source src="{{ $preview['src'] }}">
            </video>
            @break
        @case('audio')
            <audio controls style="width: 100%;">
                <source src="{{ $preview['src'] }}">
            </audio>
            @break
        @case('pdf')
            <embed src="{{ $preview['src'] }}" type="application/pdf" style="width: 100%; height: 600px;" />
            @break
        @case('sheet')
            <div style="overflow-x: auto;">
                <table class="fi-ta-table" style="width: 100%;">
                    <tbody>
                        @foreach ($preview['rows'] ?? [] as $row)
                            <tr>
                                @foreach ($row as $value)
                                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid rgba(127, 127, 127, 0.2); white-space: nowrap;">{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @break
        @case('too-large')
            <p>{{ trans('filament-browser::messages.preview.too-large') }}</p>
            @break
    @endswitch
</div>
