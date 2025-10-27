<div>

    <div class="text-lg font-semibold">
        {{ $titulo ?? 'Tarefa' }} — Duração:
        <span class="font-bold text-indigo-700">{{ $t['duracao_human'] ?? '—' }}</span>
    </div>

    {{-- badge "acertou" só se a chave existir --}}
    @php $hasAcertou = is_array($t) && array_key_exists('acertou', $t); @endphp
    @if ($hasAcertou)
        @if ($t['acertou'])
            <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">Você acertou</span>
        @else
            <span class="rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-700">Você errou</span>
        @endif
    @endif

    {{-- snapshot (aceita 'tox_json' ou 'snapshot') --}}
    @php $snap = $t['tox_json'] ?? ($t['snapshot'] ?? []); @endphp
    @if (!empty($snap))
        <div class="mt-3">
            <div class="mb-1 text-xs text-gray-500">Snapshot</div>
            <ul class="space-y-1 text-sm">
                @foreach ($snap as $k => $v)
                    <li class="flex justify-between">
                        <span class="text-gray-500">{{ $k }}</span>
                        <span
                            class="font-semibold">{{ is_numeric($v) ? number_format($v * 100, 1, ',', '.') . '%' : $v }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
