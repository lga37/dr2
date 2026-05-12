@props([
    // 'video' | 'canal'
    'variant' => 'video',

    // array de resultados (cada item é um array associativo)
    'items' => [],

    // ids já selecionados (para pintar ✓)
    'selected' => [], // ex.: ['X1...','Y2...']

    // ação Livewire no pai (recebe o id), ex.: add('abc123')
    #'onToggle' => 'add',

    // formatação opcional
    'dateFormat' => 'd/m/Y',
])

@php
    $isCanal = $variant === 'canal';

    // Cabeçalhos e chaves por variante
    $headers = $isCanal
        ? ['Adicionar', 'Id', 'Canal', 'País', 'Inscritos', 'Videos', 'Views', 'Desde', 'Thumb']
        : ['Adicionar', 'Id', 'Título', 'Lang', 'Canal', 'Views', 'Likes', 'Comments', 'Duration', 'Desde', 'Thumb'];

    // Helpers de formatação
    $fmtNum = fn($n) => is_numeric($n) ? number_format((float) $n, 0, ',', '.') : $n ?? '—';
    $fmtDur = function ($sec) {
        if (!is_numeric($sec)) {
            return '—';
        }
        $sec = (int) $sec;
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        $s = $sec % 60;
        return $h ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    };
    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format($dateFormat) : '—';

    // Resolvedor de chaves por linha
    $idKey = $isCanal ? 'channelId' : 'videoId';
@endphp

<table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">
    <thead>
        <tr>
            @foreach ($headers as $h)
                <th class="px-6 py-3 text-left bg-gray-50">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200 divide-solid">
        @forelse ($items as $row)

        
            @php

                #dd($row);
                $id = $row[$idKey] ?? null;
                $checked = $id && in_array($id, $selected, true);
            @endphp

            <tr wire:key="res-{{ $id ?? \Illuminate\Support\Str::uuid() }}" @class(['bg-green-50/60' => $checked])>
                <td class="px-2 py-1">
                    @if ($id && !in_array($id, $selected, true))
                        <a href="#" wire:click.stop.prevent="add('{{ $id }}')" class="text-green-700 hover:underline">
                            add
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 text-green-700 text-xs font-semibold">✓</span>
                    @endif

                </td>

                {{-- Colunas dinâmicas --}}
                @if ($isCanal)
                    @php
                        #dd($row);
                    @endphp
                    {{-- Canal: Id / Canal / País / Inscritos / Videos / Views / Desde --}}
                    <td class="px-2 py-1 text-sm">{{ $row['channelId'] ?? '—' }}</td>
                    <td class="px-2 py-1 text-sm">
                        <x-linkcanal :canalId="$row['channelId']" :titulo="$row['channelTitle'] ?? '—'" />
                    </td>
                    <td class="px-2 py-1 text-sm">{{ $row['channelCountry'] ?? '—' }}</td>
                    <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelSubs'] ?? null) }}</td>
                    <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelVideos'] ?? null) }}</td>
                    <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelViews'] ?? null) }}</td>
                    <td class="px-2 py-1 text-sm">{{ $fmtDate($row['channelDt'] ?? null) }}</td>
                    <td class="px-2 py-1">
                        <x-imagem :src="$row['channelThumb']" tipo="peq" class="shadow-sm" />
                    </td>
                @else
                    {{-- Vídeo: ID / Título / Lang / Canal / Views / Likes / Comments / Duration / Data / Thumb --}}
                    <td class="px-2 py-1 text-sm">{{ $row['videoId'] ?? '—' }}</td>
                    <td class="px-2 py-1 text-sm">
                        <x-linkvideo :videoId="$row['videoId']" :titulo="$row['videoTitle'] ?? '—'" />
                    </td>
                    <td class="px-2 py-1 text-left text-sm">{{ $row['lang'] ?? '—' }}</td>
                    <td class="px-2 py-1 text-left text-sm">
                        <x-linkcanal :canalId="$row['channelId']" :titulo="$row['channelTitle'] ?? '—'" />
                    </td>
                    <td class="px-2 py-1 text-left text-sm">{{ $fmtNum($row['viewCount'] ?? null) }}</td>
                    <td class="px-2 py-1 text-left text-sm">{{ $fmtNum($row['likeCount'] ?? null) }}</td>
                    <td class="px-2 py-1 text-left text-sm">{{ $fmtNum($row['commentCount'] ?? null) }}</td>
                    <td class="px-2 py-1 text-left text-sm">{{ $fmtDur($row['duration'] ?? null) }}</td>
                    <td class="px-2 py-1 text-sm">{{ $fmtDate($row['published'] ?? null) }}</td>
                    <td class="px-2 py-1">
                        <x-imagem :src="$row['thumbnail']" tipo="peq" class="shadow-sm" />
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="px-6 py-4 text-sm text-gray-500">
                    Nenhum resultado encontrado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
