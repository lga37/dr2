 {{-- @props([
    'items' => [],          // array de strings
    'more'  => 0,           // inteiro: +N extra
    'rows'  => 2,           // quantas linhas visíveis (1..3)
    'maxChipWidth' => '12rem', // largura máx. de cada chip
])

@php
    // altura do chip (Tailwind: h-7 = ~28px)
    $chipH = 'h-7';

    // altura máxima do wrapper conforme nº de linhas
    $wrapperH = match((int) $rows) {
        1 => 'h-7',
        3 => 'h-20', // ~84px
        default => 'h-14', // 2 linhas ~56px
    };
@endphp

@if (!empty($items))
<div class="relative {{ $wrapperH }} flex flex-wrap gap-2 mb-2 overflow-hidden hover:overflow-y-auto pr-1">
    @foreach ($items as $kwd)
        <span
            class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                   text-[11px] bg-gray-50 text-gray-700 hover:bg-gray-100
                   whitespace-nowrap truncate"
            style="max-width: {{ $maxChipWidth }};"
            title="{{ $kwd }}"
        >
            {{ $kwd }}
        </span>
    @endforeach

    @if (($more ?? 0) > 0)
        <span
            class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                   text-[11px] bg-indigo-50 text-indigo-700"
            title="+{{ $more }} keywords escondidas"
        >
            +{{ $more }}
        </span>
    @endif
</div>
@endif --}}


@props([
    'items'        => [],            // array de strings
    'more'         => 0,             // inteiro: +N extra
    'rows'         => 2,             // 1..3 (linhas visíveis)
    'maxChipWidth' => '12rem',       // CSS: max-width de cada chip
    'expandable'   => true,          // mostra botão ver mais/menos
    'bg'           => 'white',       // cor do fundo pro fade: white | gray-50 etc.
])

@php
    // altura do chip ( ~28px )
    $chipH = 'h-7';

    // altura do wrapper por nº de linhas
    $wrapperH = match((int) $rows) {
        1 => 'h-7',      // ~28px
        3 => 'h-20',     // ~84px
        default => 'h-14', // ~56px (2 linhas)
    };

    // Estilo base do wrapper com altura fixa e overflow escondido
    $baseWrapper = "flex flex-wrap gap-2 mb-2 pr-1";
@endphp

@if (!empty($items))
<div x-data="{ open: false }" class="relative">
    {{-- LISTA DE CHIPS --}}
    <div
        :class="open ? '{{ $baseWrapper }} max-h-none overflow-visible' : '{{ $baseWrapper }} {{ $wrapperH }} overflow-hidden'"
    >
        @foreach ($items as $i => $kwd)
            @php $kwd = (string) $kwd; @endphp
            <span
                class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                       text-[11px] leading-none bg-gray-50 text-gray-700 hover:bg-gray-100
                       whitespace-nowrap truncate"
                style="max-width: {{ $maxChipWidth }};"
                title="{{ $kwd }}"
            >
                {{ $kwd }}
            </span>
        @endforeach

        @if (($more ?? 0) > 0)
            <span
                class="inline-flex items-center {{ $chipH }} rounded-md border px-2
                       text-[11px] leading-none bg-indigo-50 text-indigo-700"
                title="+{{ $more }} keywords escondidas"
            >
                +{{ $more }}
            </span>
        @endif
    </div>

    {{-- FADE no rodapé quando fechado (sugere que há mais conteúdo) --}}
    @if ($expandable)
        <div
            x-show="!open"
            class="pointer-events-none absolute inset-x-0 bottom-0 h-6
                   bg-gradient-to-t from-{{ $bg }} to-transparent"
        ></div>

        {{-- Botão ver mais / ver menos --}}
        <button
            type="button"
            x-on:click="open = !open"
            class="absolute -bottom-2 right-0 text-[11px] px-2 py-0.5
                   rounded-md border bg-gray-50 hover:bg-gray-100
                   text-gray-700 shadow-sm"
            aria-label="Alternar exibição de keywords"
        >
            <span x-show="!open">ver mais</span>
            <span x-show="open">ver menos</span>
        </button>
    @endif
</div>
@endif


{{-- 1 linha, chips com largura máx 10rem, com botão ver mais --}}
{{-- <x-keywords :items="$videoTags" :more="$tagsMore" rows="1" maxChipWidth="10rem" /> --}}

{{-- 2 linhas, sem botão (fica só com overflow escondido) --}}
{{-- <x-keywords :items="$channelKeywords" :more="$kwMore" :expandable="false" rows="2" /> --}}

{{-- 3 linhas, fade combinando com bg cinza do card --}}
{{-- <x-keywords :items="$kw" bg="gray-50" rows="3" /> --}}