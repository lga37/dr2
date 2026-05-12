{{-- resources/views/livewire/resultados.blade.php --}}
<div x-data="{ tab: 'T1' }">

    {{-- Box explicativo --}}
    <div class="bg-white border rounded-2xl p-5 md:p-6 shadow-sm mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-slate-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14" stroke="currentColor" stroke-width="1.5" />
                    <path d="M7 8h10M7 12h10M7 16h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </div>

            <div>
                <h3 class="text-base md:text-lg font-semibold">Página de resultados (anonimizada)</h3>
                <p class="mt-1 text-slate-600 text-sm max-w-12xl">
                    Os resultados são exibidos por tarefa, contendo início/fim e os dados processados
                    (canais, vídeos, comentários e métricas), além do feedback textual quando existir.
                    Não há identificação de participantes.
                </p>
            </div>
        </div>
    </div>

    <x-msg />

    {{-- Tabs --}}
    <ul class="flex flex-wrap gap-3 text-sm mb-4">
        @foreach (['T1', 'T2', 'T3', 'T4'] as $k)
            <li>
                <button @click="tab='{{ $k }}'"
                    class="px-3 py-1.5 rounded-lg border bg-white hover:bg-slate-50"
                    :class="tab === '{{ $k }}' ? 'font-semibold ring-2 ring-slate-200' : ''">
                    {{ $k }} ({{ $qtd[$k] }})
                </button>
            </li>
        @endforeach
    </ul>

    {{-- Panels --}}
    @foreach (['T1', 'T2', 'T3', 'T4'] as $k)
        <div x-show="tab==='{{ $k }}'" x-cloak class="space-y-3">

            @forelse ($itensByTipo[$k] as $t)
                @php
                    // TUDO está dentro de payload
                    $p = $t['payload'] ?? [];

                    $tipo = $t['tipo'] ?? $k;

                    $buscas = $p['buscas'] ?? [];
                    $canais = $p['canais'] ?? [];
                    $videos = $p['videos'] ?? []; // T1/T2/T4
                    $comentarios = $p['comentarios'] ?? []; // Só T1 (no seu requisito)

                    $feedback = trim((string) ($p['feedback'] ?? ''));
                @endphp

                {{-- Accordion item --}}
                <div x-data="{ open: false }" class="bg-white border rounded-2xl shadow-sm">
                    {{-- Header --}}
                    <button type="button" @click="open=!open"
                        class="w-full text-left p-4 md:p-5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                                    {{ $tipo }}
                                </span>
                                <span class="text-sm text-slate-700">
                                    #{{ $t['id'] ?? '—' }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    Início: {{ $t['inicio'] ?? '—' }} · Fim: {{ $t['fim'] ?? '—' }}
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-slate-500">
                                Duração: <span
                                    class="font-medium text-slate-700">{{ $t['duracao_human'] ?? '—' }}</span>
                            </div>
                        </div>

                        <svg class="w-5 h-5 shrink-0 text-slate-500 transition-transform"
                            :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    {{-- Body --}}
                    <div x-show="open" class="border-t">
                        <div class="p-4 md:p-5 space-y-6">

                            {{-- FEEDBACK --}}
                            @if ($feedback !== '')
                                <div class="rounded-xl border bg-amber-50/60 p-4">
                                    <div class="text-xs font-semibold text-amber-900 mb-1">Feedback</div>
                                    <div class="text-sm text-amber-900 whitespace-pre-wrap">{{ $feedback }}</div>
                                </div>
                            @endif

                            {{-- BUSCAS --}}
                            @if (!empty($buscas))
                                <div class="rounded-xl border p-4">
                                    <div class="text-xs font-semibold text-slate-700 mb-2">Consultas</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($buscas as $q)
                                            <span
                                                class="text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-700">{{ $q }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- CANAIS --}}
                            @if (!empty($canais))
                                <div class="rounded-xl border p-4">
                                    <div class="text-xs font-semibold text-slate-700 mb-3">Canais</div>

                                    <div class="space-y-2">
                                        @forelse ($canais as $c)
                                            <div class="rounded-lg border bg-white p-3">
                                                <div class="text-sm font-semibold text-slate-800">
                                                    {{ $c['nome'] ?? '—' }}</div>

                                                <div class="text-xs text-slate-500 mt-1">


                                                    <div class="text-xs text-slate-500 mt-1">
                                                        @if (isset($c['inscritos']))
                                                            Inscritos: {{ $c['inscritos'] }} ·
                                                        @endif
                                                        @if (isset($c['views']))
                                                            Views: {{ $c['views'] }} ·
                                                        @endif

                                                        @if (isset($c['videos']))
                                                            Vídeos:
                                                            {{ is_array($c['videos']) ? count($c['videos']) : $c['videos'] }}
                                                        @endif

                                                        @if (isset($c['videos_qt']))
                                                            · Vídeos:
                                                            {{ is_array($c['videos_qt']) ? count($c['videos_qt']) : $c['videos_qt'] }}
                                                        @endif
                                                    </div>

                                                </div>

                                                {{-- T3: vídeos estão DENTRO do canal --}}
                                                @if ($tipo === 'T3' && !empty($c['videos']) && is_array($c['videos']))
                                                    <div class="mt-3 overflow-x-auto">
                                                        <table class="min-w-[860px] w-full text-sm">
                                                            <thead class="text-xs text-slate-500">
                                                                <tr class="border-b">
                                                                    <th class="text-left py-2 pr-3">Data</th>
                                                                    <th class="text-left py-2 pr-3">Vídeo</th>
                                                                    <th class="text-right py-2 pr-3">Views</th>
                                                                    <th class="text-right py-2 pr-3">Likes</th>
                                                                    <th class="text-right py-2">Comentários</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y">
                                                                @foreach ($c['videos'] as $v)
                                                                    <tr>
                                                                        <td class="py-2 pr-3 text-slate-600">
                                                                            {{ $v['dt'] ?? '—' }}</td>
                                                                        <td class="py-2 pr-3">
                                                                            <div class="font-medium text-slate-800">
                                                                                {{ $v['nome'] ?? '—' }}</div>
                                                                            <div class="text-xs text-slate-500">
                                                                                {{ $v['cod'] ?? '' }}</div>
                                                                        </td>
                                                                        <td class="py-2 pr-3 text-right text-slate-700">
                                                                            {{ $v['views'] ?? 0 }}</td>
                                                                        <td class="py-2 pr-3 text-right text-slate-700">
                                                                            {{ $v['likes'] ?? 0 }}</td>
                                                                        <td class="py-2 text-right text-slate-700">
                                                                            {{ $v['comments'] ?? 0 }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-sm text-slate-500">Sem canais.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- VÍDEOS (T1/T2/T4 usam payload.videos) --}}
                            @if ($tipo !== 'T3' && !empty($videos))
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
                                            @forelse ($videos as $v)
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $v['dt'] ?? '—' }}</td>
                                                    <td class="py-2 pr-3">
                                                        <div class="font-medium text-slate-800">{{ $v['nome'] ?? '—' }}
                                                        </div>
                                                        <div class="text-xs text-slate-500">{{ $v['cod'] ?? '' }}</div>
                                                    </td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $v['canal'] ?? '—' }}</td>
                                                    <td class="py-2 pr-3 text-right text-slate-700">
                                                        {{ $v['views'] ?? 0 }}</td>
                                                    <td class="py-2 pr-3 text-right text-slate-700">
                                                        {{ $v['likes'] ?? 0 }}</td>
                                                    <td class="py-2 text-right text-slate-700">
                                                        {{ $v['comments'] ?? 0 }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-4 text-center text-slate-500">Sem
                                                        vídeos.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- COMENTÁRIOS (só T1, conforme teu requisito) --}}
                            @if ($tipo === 'T1' && !empty($comentarios))
                                <div class="rounded-xl border p-4">
                                    <div class="text-xs font-semibold text-slate-700 mb-2">Comentários</div>

                                    <div class="space-y-2 max-h-[420px] overflow-auto">
                                        @forelse ($comentarios as $c)
                                            <div class="rounded-lg bg-slate-50 border p-3 text-xs text-slate-700">
                                                <div class="flex flex-wrap gap-3 text-slate-600">
                                                    <span><b>dt</b>: {{ $c['dt'] ?? '—' }}</span>
                                                    <span><b>video_id</b>: {{ $c['video_id'] ?? '—' }}</span>
                                                    <span><b>likes</b>: {{ $c['likes'] ?? 0 }}</span>
                                                    <span><b>tox</b>: {{ $c['tox'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-sm text-slate-500">Sem comentários.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- JSON bruto (pra debug) --}}
                            @if (!empty($p['dados_json']))
                                <details class="rounded-xl border p-4">
                                    <summary class="cursor-pointer text-xs font-semibold text-slate-700">Ver JSON bruto
                                    </summary>
                                    <pre class="mt-3 text-xs whitespace-pre-wrap text-slate-700">{{ json_encode($p['dados_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif

                        </div>
                    </div>
                </div>

            @empty
                <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                    Nenhum resultado concluído para {{ $k }}.
                </div>
            @endforelse

        </div>
    @endforeach

</div>
