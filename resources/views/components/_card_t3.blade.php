{{-- resources/views/components/_card_t3.blade.php --}}
<div class="rounded-2xl border bg-white p-5 shadow-sm">
    {{-- HEADER / READER --}}
    <div class="flex items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="text-sm text-gray-500">
                #{{ $t['id'] }} • feito em {{ $t['quando'] }}
            </div>
            <div class="text-lg font-semibold">
                Duração: <span class="font-bold text-indigo-700">{{ $t['duracao_human'] }}</span>
            </div>
        </div>

        @if (!is_null($t['acertou']))
            @if ($t['acertou'])
                <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">Você acertou</span>
            @else
                <span class="rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-700">Você errou</span>
            @endif
        @endif
    </div>

    {{-- READER superior com resumo da rodada --}}
    <div class="mt-4 grid gap-3 rounded-xl border bg-slate-50 p-3 text-sm md:grid-cols-3">
        <div class="flex items-center justify-between">
            <span class="text-slate-600">Sua escolha</span>
            <span class="font-semibold truncate" title="{{ $t['escolha'] ?? '—' }}">{{ $t['escolha'] ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-600">Vencedor (USD/min)</span>
            <span class="font-semibold truncate" title="{{ $t['vencedor'] ?? '—' }}">{{ $t['vencedor'] ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-600">Canais comparados</span>
            <span class="font-semibold">{{ count($t['canais'] ?? []) }}</span>
        </div>
    </div>

    {{-- DOIS BOXES (um por canal) --}}
    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
        @foreach ($t['canais'] as $c)
            <div class="rounded-xl border p-4">
                <div class="mb-2 flex items-start justify-between gap-3">
                    <div>
                        <div class="text-base font-semibold">{{ $c['nome'] ?? 'Canal' }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $c['channel_id'] ?? '' }}
                        </div>
                    </div>

                    {{-- Selo "vencedor" --}}
                    @if (($t['vencedor'] ?? null) && $t['vencedor'] === ($c['channel_id'] ?? null))
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            Vencedor
                        </span>
                    @endif
                </div>

                {{-- métricas do canal --}}
                <div class="space-y-2 rounded-lg border p-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Inscritos</span>
                        <span class="font-semibold">
                            {{ isset($c['inscritos']) ? number_format($c['inscritos'], 0, ',', '.') : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Views do canal</span>
                        <span class="font-semibold">
                            {{ isset($c['views']) ? number_format($c['views'], 0, ',', '.') : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Vídeos (coletados)</span>
                        <span class="font-semibold">{{ $c['videos_qt'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Minutagem total</span>
                        <span class="font-semibold">{{ $c['minTotFmt'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Monetização atual</span>
                        <span class="font-semibold">
                            {{ isset($c['monetAvgUsd']) ? '$'.number_format($c['monetAvgUsd'], 2, ',', '.') . '/mês' : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Base (meses)</span>
                        <span class="font-semibold">{{ $c['monthsBase'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Área (USD)</span>
                        <span class="font-semibold">
                            {{ isset($c['areaUsd']) ? '$'.number_format((float)$c['areaUsd'], 2, ',', '.') : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">USD por minuto</span>
                        <span class="font-semibold">
                            {{ isset($c['usdPerMin']) ? '$'.number_format((float)$c['usdPerMin'], 6, ',', '.') : '—' }}
                        </span>
                    </div>
                </div>

                {{-- tabela dos vídeos do canal --}}
                @if (!empty($c['videos']))
                    <div class="mt-4 overflow-hidden rounded-lg border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-3 py-2">Vídeo</th>
                                    <th class="px-3 py-2 text-right">Views</th>
                                    <th class="px-3 py-2 text-right">Likes</th>
                                    <th class="px-3 py-2 text-right">Com.</th>
                                    <th class="px-3 py-2">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($c['videos'] as $v)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <a class="text-blue-600 hover:underline" target="_blank"
                                               href="https://www.youtube.com/watch?v={{ $v['cod'] }}">
                                                {{ $v['nome'] ?? $v['cod'] }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($v['views'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($v['likes'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($v['comments'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2">{{ $v['dt'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if (!empty($t['feedback']))
        <div class="mt-6 rounded-lg border bg-slate-50 p-3 text-sm">
            <div class="mb-1 font-semibold text-slate-700">Seu feedback</div>
            <p class="text-slate-700">{{ $t['feedback'] }}</p>
        </div>
    @endif
</div>
