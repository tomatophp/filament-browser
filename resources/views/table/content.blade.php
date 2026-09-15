<div style="padding: 1rem;">
    @if (count($records))
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr)); gap: 0.75rem;">
            @foreach ($records as $item)
                @php
                    $isFolder = $item['type'] === 'folder';
                    [$icon, $color] = \TomatoPHP\FilamentBrowser\Pages\Browser::iconFor($item);
                @endphp
                <button
                    type="button"
                    wire:key="filament-browser-{{ md5($item['path']) }}"
                    wire:click="mountAction('{{ $isFolder ? 'openFolder' : 'openFile' }}', {{ \Illuminate\Support\Js::from(['path' => $item['path']]) }})"
                    style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(127, 127, 127, 0.25); background: transparent; cursor: pointer;"
                >
                    @svg($icon, '', ['style' => "width: 4rem; height: 4rem; color: {$color};"])
                    <span style="font-weight: 500; text-align: center; word-break: break-all; color: {{ $color }};">
                        {{ $item['name'] }}@unless ($isFolder) [{{ $item['size'] }}]@endunless
                    </span>
                </button>
            @endforeach
        </div>
    @else
        <x-filament::empty-state
            :heading="trans('filament-browser::messages.empty')"
            icon="heroicon-o-x-circle"
        />
    @endif
</div>
