@props([
    'items'        => [],      // array de strings (completo)
    'limit'        => 8,       // quantas mostrar antes do "+N"
    'rows'         => 2,       // altura do wrapper quando fechado (1..3)
    'maxChipWidth' => '12rem', // CSS
    'bg'           => 'white', // p/ fade: white | gray-50 etc.
])

@php
    use Illuminate\Support\Arr;

    $all    = collect(Arr::wrap($items))->filter(fn($t) => filled($t))->map(fn($t) => (string) $t)->values();
    $limit  = max(0, (int) $limit);
    $first  = $all->slice(0, $limit);
    $rest   = $all->slice($limit)->values();
    $extraN = $rest->count();

    $chipH = 'h-7';
    $wrapperH = match((int) $rows) {
        1 => 'h-7',
        3 => 'h-7',
        default => 'h-7', // 2 linhas
    };
    $baseWrapper = "flex flex-wrap gap-2 mb-2 pr-1";
@endphp

<div x-data="{ open:false }" class="relative">

    @if ($all->isEmpty())
        {{-- placeholder quando não há keywords --}}
        <div class="{{ $baseWrapper }} {{ $wrapperH }}">
            <span class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                         text-[11px] leading-none bg-red-50 text-red-500">
                sem palavras-chave
            </span>
        </div>
    @else
        {{-- lista de chips; quando abrir, remove limite/overflow --}}
        <div :class="open ? '{{ $baseWrapper }} max-h-none overflow-visible'
                          : '{{ $baseWrapper }} {{ $wrapperH }} overflow-hidden'">

            {{-- primeiros N sempre visíveis --}}
            @foreach ($first as $kwd)
                <span class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                             text-[11px] leading-none bg-gray-50 text-gray-700 hover:bg-gray-100
                             whitespace-nowrap truncate"
                      style="max-width: {{ $maxChipWidth }};"
                      title="{{ $kwd }}">{{ $kwd }}</span>
            @endforeach

            {{-- excedentes: só aparecem após clicar no +N --}}
            @foreach ($rest as $kwd)
                <span x-show="open"
                      class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                             text-[11px] leading-none bg-gray-50 text-gray-700 hover:bg-gray-100
                             whitespace-nowrap truncate"
                      style="max-width: {{ $maxChipWidth }};"
                      title="{{ $kwd }}">{{ $kwd }}</span>
            @endforeach

            {{-- +N como CHIP- BOTÃO: um clique -> open=true; depois some --}}
            @if ($extraN > 0)
                <button type="button"
                        x-show="!open"
                        x-on:click="open = true"
                        class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                               text-[11px] leading-none bg-indigo-50 text-indigo-700 hover:bg-indigo-100
                               focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    +{{ $extraN }}
                </button>
            @endif
        </div>

        {{-- fade só enquanto estiver fechado --}}
        <div x-show="!open"
             class="pointer-events-none absolute inset-x-0 bottom-0 h-6
                    bg-gradient-to-t from-{{ $bg }} to-transparent"></div>
    @endif
</div>
