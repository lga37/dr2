@props([
    'items' => [],
    'selected' => [], // ex.: ['X1...','Y2...']
    'checked' => [], // id => true (checado = incluir)
    'dateFormat' => 'd/m/Y',
])

@php
    // Cabeçalhos e chaves por variante
    $headers = ['Adicionar', 'Id', 'Canal', 'País', 'Inscritos', 'Videos', 'Views', 'U$/mes','Desde', 'Thumb'];

    $fmtNum = fn($n) => is_numeric($n) ? number_format((float) $n, 0, ',', '.') : $n ?? '—';
    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format($dateFormat) : '—';
    $idKey = 'channelId';
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
                $id = $row['channelId'] ?? null;
                $isSelectedVisually = $id && !in_array($id, $selected, true);
                $checkedArr = $checked ?? [];
            @endphp
            <tr wire:key="res-{{ $id ?? \Illuminate\Support\Str::uuid() }}" @class(['bg-green-50/60' => $checked])>
                <td class="px-2 py-1">
                    @if ($id)
                        <label class="inline-flex items-center gap-2">
                            {{-- <input type="checkbox" class="rounded border-gray-300"
                                wire:model.live="checked.{{ $id }}" @checked($checkedArr[$id] ?? true)
                                title="Incluir este canal"> --}}


                            {{-- <input type="checkbox" class="rounded border-gray-300" 
                            @checked($checkedArr[$id] ?? true)
                                wire:change="toggleCheck('{{ $id }}', $event.target.checked)"> --}}


                            <input type="checkbox" class="rounded border-gray-300"
                                wire:model.live="checked.{{ $id }}" 
                                @checked(($checked[$id] ?? true) && !($unchecked[$id] ?? false))
                                title="Incluir este canal">


                            <span class="text-xs text-gray-700">Incluir</span>

                        </label>
                    @else
                        —
                    @endif
                </td>

                {{-- Canal: Id / Canal / País / Inscritos / Videos / Views / Desde --}}
                <td class="px-2 py-1 text-sm">{{ $row['channelId'] ?? '—' }}</td>
                <td class="px-2 py-1 text-sm"><x-linkcanal :canalId="$row['channelId']" :titulo="$row['channelTitle'] ?? '—'" /></td>
                <td class="px-2 py-1 text-sm">{{ $row['channelCountry'] ?? '—' }}</td>
                <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelSubs'] ?? null) }}</td>
                <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelVideos'] ?? null) }}</td>
                <td class="px-2 py-1 text-sm">{{ $fmtNum($row['channelViews'] ?? null) }}</td>
                <td class="px-2 py-1 text-sm"><a href="https://vidiq.com/youtube-stats/channel/{{ $row['channelId']}}" target="_blank">Vidiq</a></td>


                
                <td class="px-2 py-1 text-sm">{{ $fmtDate($row['channelDt'] ?? null) }}</td>
                <td class="px-2 py-1"><x-imagem :src="$row['channelThumb']" tipo="peq" class="shadow-sm" /></td>
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
