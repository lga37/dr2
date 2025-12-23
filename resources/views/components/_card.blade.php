{{-- resources/views/components/_resultado_widget.blade.php --}}
@php($p = $t['payload'] ?? [])

<div class="space-y-5">

    {{-- Feedback --}}
    @if (!empty($p['feedback']))
        <div class="rounded-xl border bg-amber-50/60 p-4">
            <div class="text-xs font-semibold text-amber-900 mb-1">Feedback</div>
            <div class="text-sm text-amber-900 whitespace-pre-wrap">{{ $p['feedback'] }}</div>
        </div>
    @endif

    {{-- Métricas rápidas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @if (isset($p['comentarios_qt']))
            <div class="rounded-xl border p-3">
                <div class="text-xs text-slate-500">Comentários</div>
                <div class="text-sm font-semibold">{{ $p['comentarios_qt'] }}</div>
            </div>
        @endif

        @if (isset($p['tox_media']))
            <div class="rounded-xl border p-3">
                <div class="text-xs text-slate-500">Toxicidade média</div>
                <div class="text-sm font-semibold">{{ $p['tox_media'] }}</div>
            </div>
        @endif

        @if (array_key_exists('acertou', $p))
            <div class="rounded-xl border p-3">
                <div class="text-xs text-slate-500">Acertou</div>
                <div class="text-sm font-semibold">
                    @if ($p['acertou'] === null) —
                    @else {{ $p['acertou'] ? 'Sim' : 'Não' }}
                    @endif
                </div>
            </div>
        @endif

        @if (isset($p['buscas']) && is_array($p['buscas']))
            <div class="rounded-xl border p-3">
                <div class="text-xs text-slate-500">Buscas</div>
                <div class="text-sm font-semibold">{{ count($p['buscas']) }}</div>
            </div>
        @endif
    </div>

    {{-- Buscas --}}
    @if (!empty($p['buscas']) && is_array($p['buscas']))
        <div class="rounded-xl border p-4">
            <div class="text-xs font-semibold text-slate-700 mb-2">Consultas</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($p['buscas'] as $q)
                    <span class="text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-700">{{ $q }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Canais --}}
    @if (!empty($p['canais']) && is_array($p['canais']))
        <div class="rounded-xl border p-4">
            <div class="text-xs font-semibold text-slate-700 mb-3">Canais</div>
            <div class="space-y-2">
                @foreach ($p['canais'] as $c)
                    <div class="rounded-lg border bg-white p-3">
                        <div class="text-sm font-semibold text-slate-800">{{ $c['nome'] ?? '—' }}</div>
                        <div class="text-xs text-slate-500 mt-1">
                            @if(isset($c['inscritos'])) Inscritos: {{ $c['inscritos'] }} · @endif
                            @if(isset($c['views'])) Views: {{ $c['views'] }} · @endif
                            @if(isset($c['videos'])) Vídeos: {{ $c['videos'] }} @endif
                        </div>

                        {{-- extras T3 --}}
                        @if(isset($c['monetAvgUsd']))
                            <div class="text-xs text-slate-600 mt-2">
                                Monetização média (USD): <span class="font-medium">{{ $c['monetAvgUsd'] }}</span>
                                @if(isset($c['usdPerMin'])) · USD/min: <span class="font-medium">{{ $c['usdPerMin'] }}</span>@endif
                                @if(isset($c['minTotFmt'])) · Minutagem: <span class="font-medium">{{ $c['minTotFmt'] }}</span>@endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Vídeos --}}
    @if (!empty($p['videos']) && is_array($p['videos']))
        <div class="rounded-xl border p-4 overflow-x-auto">
            <div class="text-xs font-semibold text-slate-700 mb-3">Vídeos</div>

            <table class="min-w-[900px] w-full text-sm">
                <thead class="text-xs text-slate-500">
                    <tr class="border-b">
                        <th class="text-left py-2 pr-3">Data</th>
                        <th class="text-left py-2 pr-3">Vídeo</th>
                        <th class="text-left py-2 pr-3">Canal</th>
                        <th class="text-right py-2 pr-3">Views</th>
                        <th class="text-right py-2 pr-3">Likes</th>
                        <th class="text-right py-2">Comentários</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($p['videos'] as $v)
                        <tr>
                            <td class="py-2 pr-3 text-slate-600">{{ $v['dt'] ?? '—' }}</td>
                            <td class="py-2 pr-3">
                                <div class="font-medium text-slate-800">{{ $v['nome'] ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $v['cod'] ?? '' }}</div>
                            </td>
                            <td class="py-2 pr-3 text-slate-700">{{ $v['canal'] ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right text-slate-700">{{ $v['views'] ?? 0 }}</td>
                            <td class="py-2 pr-3 text-right text-slate-700">{{ $v['likes'] ?? 0 }}</td>
                            <td class="py-2 text-right text-slate-700">{{ $v['comments'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- JSON bruto --}}
    @if (!empty($p['dados_json']))
        <details class="rounded-xl border p-4">
            <summary class="cursor-pointer text-xs font-semibold text-slate-700">Ver JSON bruto</summary>
            <pre class="mt-3 text-xs whitespace-pre-wrap text-slate-700">{{ json_encode($p['dados_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif

</div>
