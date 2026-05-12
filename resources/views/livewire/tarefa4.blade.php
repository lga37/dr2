<div>
    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-emerald-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 18V7m5 11V5m5 13V9m5 9V4" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    WIDGET 4 — Intersecção entre <span class="text-emerald-700">polarizacao</span>, <span
                        class="text-emerald-700">toxicidade</span> e <span class="text-emerald-700">monetização</span>
                </h2>
            </div>
        </div>

        {{-- bloco resumido --}}
<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- explicação --}}
    <div class="lg:col-span-2 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-emerald-800 mb-3 uppercase tracking-wide">
            Como utilizar o Widget 4
        </h3>

        <div class="space-y-2 text-sm text-slate-700 leading-6">

            <p>
                Pesquise vídeos relacionados a um mesmo tema,
                selecione múltiplos registros
                e clique em <strong>adicionar checkados</strong>.
            </p>

            <p>
                O sistema avalia simultaneamente
                <strong>polarização</strong>,
                <strong>toxicidade</strong>
                e indicadores de
                <strong>monetização</strong>
                em função da query pesquisada.
            </p>

            <p>
                A síntese permite observar padrões de
                discurso, monetização externa,
                estimativas financeiras,
                evolução temporal dos comentários
                e possíveis relações entre engajamento,
                radicalização e receita.
            </p>

        </div>
    </div>

    {{-- legenda --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 uppercase tracking-wide">
            Legenda
        </h3>

        <div class="space-y-2 text-sm">

            <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                <span class="text-slate-600">Polarização</span>
                <span class="font-semibold text-blue-700">Categoria / polo</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-rose-100 bg-rose-50 px-3 py-2">
                <span class="text-slate-600">Toxicidade</span>
                <span class="font-semibold text-rose-700">Comentários</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                <span class="text-slate-600">Monetização</span>
                <span class="font-semibold text-emerald-700">VidIQ / URLs</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-3 py-2">
                <span class="text-slate-600">Síntese PMT</span>
                <span class="font-semibold text-amber-700">Intersecção geral</span>
            </div>

        </div>
    </div>

</div>

    </div>


    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">

                <x-selecionados-table :items="$selecionados" type="video" remove="removeSelecionado"
                    clear="clearSelecionados" evaluate="avaliarVideos" :min="2" :max="3" />
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">
                    <x-search-add-bar variant="video" query-model="query" on-search="pesquisar" add-model="addInput"
                        on-add="addVideoByInput" />
                    <x-results-table-check :items="$buscas" :checked="$checked" />

                </div>
            </div>


            <div class="mt-8 px-4">
                <div class="flex flex-col md:flex-row gap-6">
                    {{-- PASSO 1 --}}
                    <div id="avaliacao" class="flex-1">
                        <x-primary-button wire:click="addTodos" wire:loading.attr="disabled" wire:target="addTodos"
                            class="w-full flex items-center justify-start gap-2 px-3 py-4 rounded-2xl text-left
                       bg-emerald-100 hover:bg-emerald-200 border border-emerald-200">

                            {{-- número grande --}}
                            <span
                                class="flex items-center justify-center h-16 w-16 rounded-full
                             bg-emerald-500 text-white text-4xl font-extrabold leading-none">
                                1
                            </span>

                            {{-- textos à esquerda, colados no número --}}
                            <div class="flex flex-col">
                                <span class="text-lg font-semibold">
                                    Adicionar Checkados
                                </span>
                                <span class="text-xs text-emerald-700">
                                    Depois clique em 'Avaliar Canais'
                                </span>
                            </div>

                            {{-- loading à direita --}}
                            <span class="ml-auto invisible" wire:loading.class.remove="invisible"
                                wire:target="addTodos">
                                <span class="text-sm text-yellow-500">
                                    Aguarde processando...
                                </span>
                            </span>
                        </x-primary-button>
                    </div>


                </div>
            </div>
            
            

            @if ($mostrarFeedback)
                <div class="mt-4 grid gap-2">
                    <label class="text-sm font-medium text-gray-700">
                        Deixe um breve feedback:
                    </label>
                    <textarea rows="3" wire:model.defer="feedback"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ex.: Pelo nome e engajamento (views, data de criação, tags, etc.) , achei que este canal seria mais toxico e assim tambem teria maior rendimento financeiro."></textarea>

                    <div class="flex items-center gap-3">
                        <x-primary-button wire:click="salvarFeedback" wire:loading.attr="disabled"
                            wire:target="salvarFeedback">
                            Enviar feedback
                        </x-primary-button>

                        <div class="invisible" wire:loading.class.remove="invisible" wire:target="salvarFeedback">
                            <span class="text-sm text-gray-500">Salvando…</span>
                        </div>
                    </div>


                </div>
          
            @endif


        </div>

    </div>


    @php
        $t4 = session('t4_result', []);
        $overview = $t4['overview'] ?? [];
        $query = $t4['query'] ?? ($query ?? '');
    @endphp

    @if (!empty($t4))
        <div class="mt-10 mx-auto max-w-7xl px-6">

            {{-- QUERY CENTRAL --}}
            <div class="text-center mb-8">
                <div class="text-sm uppercase tracking-widest text-slate-500">
                    Tema analisado
                </div>
                <h1 class="text-6xl font-extrabold text-slate-900">
                    {{ $query }}
                </h1>
            </div>

            {{-- BOXES SUPERIORES --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                {{-- POLARIZAÇÃO --}}
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold mb-4 text-blue-800">
                        Polarização
                    </h2>

                    @php $pol = $overview['polarizacao'] ?? []; @endphp



                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-slate-500">Categoria</div>
                            <div class="font-bold">{{ $pol['categoria'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-slate-500">Polo</div>
                            <div class="font-bold">{{ $pol['polo_dominante'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-slate-500">Score</div>
                            <div class="font-bold">{{ $pol['polarizacao_score'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-slate-500">Confiança</div>
                            <div class="font-bold">{{ $pol['confianca'] ?? '-' }}</div>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-slate-600">
                        {{ $pol['justificativa'] ?? '' }}
                    </p>
                </div>

                {{-- MONETIZAÇÃO --}}
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold mb-4 text-emerald-800">
                        Monetização
                    </h2>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-slate-500">Média</div>
                            <div class="font-bold">
                                {{ isset($overview['monet_media']) ? 'US$ ' . number_format($overview['monet_media'], 0, ',', '.') : '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-slate-500">Máxima</div>
                            <div class="font-bold">
                                {{ isset($overview['monet_max']) ? 'US$ ' . number_format($overview['monet_max'], 0, ',', '.') : '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-slate-500">URLs externas</div>
                            <div class="font-bold">{{ $overview['urls_total'] ?? 0 }}</div>
                        </div>

                        <div>
                            <div class="text-slate-500">Vídeos</div>
                            <div class="font-bold">{{ $overview['videos'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOXICIDADE / OVERVIEW --}}
            <div class="rounded-2xl border bg-slate-900 text-white p-6 shadow-sm mb-8">
                <h2 class="text-xl font-bold mb-4">
                    Síntese PMT
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                    <div>
                        <div class="text-slate-400">Toxicidade média</div>
                        <div class="text-2xl font-bold">
                            {{ isset($overview['tox_media']) ? number_format($overview['tox_media'] * 100, 2, ',', '.') . '%' : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400">Toxicidade máxima</div>
                        <div class="text-2xl font-bold">
                            {{ isset($overview['tox_max']) ? number_format($overview['tox_max'] * 100, 2, ',', '.') . '%' : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400">Canais</div>
                        <div class="text-2xl font-bold">{{ $overview['canais'] ?? 0 }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400">Vídeos</div>
                        <div class="text-2xl font-bold">{{ $overview['videos'] ?? 0 }}</div>
                    </div>

                    <div>

                        <div class="text-slate-400">Comentários analisados</div>
                        <div class="text-2xl font-bold">{{ $overview['comentarios_analisados'] ?? 0 }}</div>

                    </div>
                </div>
            </div>

            {{-- MINI BOXES POR CANAL --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                @foreach ($t4['canais'] ?? [] as $canal)
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <x-linkcanal :canalId="$canal['channelId']" :titulo="$canal['channelTitle'] ?? '—'" />

                        <div class="mt-3 text-sm grid grid-cols-2 gap-2">
                            <div>Vídeos analisados: {{ count($canal['videos'] ?? []) }}</div>
                            <div>Views dos vídeos: {{ number_format($canal['views'] ?? 0, 0, ',', '.') }}</div>
                            <div>Likes dos vídeos: {{ number_format($canal['likes'] ?? 0, 0, ',', '.') }}</div>
                            <div>URLs nas descrições: {{ $canal['external_urls_count'] ?? 0 }}</div>
                        </div>
                        <div>
                            <a href="https://vidiq.com/youtube-stats/channel/{{ $canal['channelId'] }}/"
                                target="_blank" class="text-sm text-blue-600 underline">
                                VidIQ:
                                @if (!empty($canal['vidiq_monthly_avg_usd']))
                                    US$ {{ number_format($canal['vidiq_monthly_avg_usd'] ?? 0, 0, ',', '.') }}
                                @else
                                    sem dados
                                @endif
                            </a>
                        </div>


                        <div class="mt-3 text-xs text-slate-500">
                            <div class="font-semibold text-slate-700">Vídeos analisados:</div>

                            @foreach ($canal['videos'] ?? [] as $videoId)
                                <div class="font-mono">
                                    
                        <x-linkvideo :videoId="$videoId" :titulo="$videoId" />

                                    
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    @endif

    @php
        $chart = $this->chart ?? [];

        if (empty($chart) && !empty($pmtTemaResult['toxicidade']['chart'])) {
            $chart = $pmtTemaResult['toxicidade']['chart'];
        }
    @endphp

    <div class="mx-auto p-6 w-full max-w-[1400px]">
        <h2 class="text-xl font-semibold mb-4">Toxicidade (0–100%) no tempo</h2>
        <div class="w-full h-96 md:h-[420px]">
            <canvas id="toxMultiChart" class="w-full h-full"></canvas>
        </div>
    </div>


</div>

@push('scripts')
    <script>
        (function() {
            // Registro global p/ evitar gráficos fantasma
            if (!window._toxCharts)
                window._toxCharts = {};

            // Plugin de linhas verticais
            const verticalLines = {
                id: 'verticalLines',
                afterDatasetsDraw(chart, _args, opts) {
                    const {
                        ctx,
                        scales: {
                            x,
                            y
                        }
                    } = chart;
                    (opts?.markers || []).forEach(m => {
                        if (typeof m.x !== 'number') return;
                        const xp = x.getPixelForValue(m.x);
                        ctx.save();
                        ctx.strokeStyle = m.color || '#999';
                        ctx.globalAlpha = 0.6;
                        ctx.lineWidth = 1;
                        ctx.setLineDash([2, 2]);
                        ctx.beginPath();
                        ctx.moveTo(xp, y.top);
                        ctx.lineTo(xp, y.bottom);
                        ctx.stroke();
                        ctx.restore();
                    });
                }
            };

            // Helpers
            const addDays = (d, n) => {
                const x = new Date(d);
                x.setDate(x.getDate() + n);
                return x;
            };
            const fmt = (date) => {
                const dd = String(date.getDate()).padStart(2, '0');
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const yy = date.getFullYear();
                return `${dd}/${mm}/${yy}`;
            };

            // Paleta com mais cores (caso tenha >3 vídeos)
            const palette = [{
                    pt: '#22c55e',
                    line: '#15803d'
                }, // verde
                {
                    pt: '#ef4444',
                    line: '#991b1b'
                }, // vermelho
                {
                    pt: '#3b82f6',
                    line: '#1e40af'
                }, // azul
                {
                    pt: '#f59e0b',
                    line: '#b45309'
                }, // amber
                {
                    pt: '#8b5cf6',
                    line: '#5b21b6'
                }, // violet
            ];

            function renderChart(chartData, elId = 'toxMultiChart', attempts = 0) {
                const el = document.getElementById(elId);
                if (!el) return;

                // Chart.js ainda não disponível? tenta novamente um pouco depois
                if (!window.Chart) {
                    if (attempts > 20) return; // evita loop infinito
                    return setTimeout(() => renderChart(chartData, elId, attempts + 1), 150);
                }

                // Validação do payload
                if (!chartData || !chartData.series || Object.keys(chartData.series).length === 0) {
                    console.warn('chartData inválido ou vazio', chartData);
                    return;
                }

                // Log correto (evita concatenar objeto em string)
                console.log('chartData:', chartData);

                // Destrói instância anterior
                if (window._toxCharts[elId]) {
                    try {
                        window._toxCharts[elId].destroy();
                    } catch (_) {}
                    delete window._toxCharts[elId];
                }

                const globalStart = new Date(chartData.globalStart);
                const vids = Object.keys(chartData.series);
                const minX = chartData.min ?? 0;
                const maxX = chartData.max ?? 0;

                // datasets + marcadores
                const datasets = [];
                const markers = [];

                vids.forEach((vid, idx) => {
                    const s = chartData.series[vid] || {};
                    const color = palette[idx % palette.length];

                    datasets.push({
                        label: s.title || vid,
                        type: 'scatter',
                        data: Array.isArray(s.points) ? s.points : [], // [{x:days,y:%,label}]
                        parsing: false,
                        showLine: false,
                        pointRadius: 3,
                        borderColor: color.pt,
                        backgroundColor: color.pt,
                    });

                    if (typeof s.avg === 'number' && isFinite(s.avg)) {
                        datasets.push({
                            label: ' — média ',
                            //label: `${s.title || vid}\n— média`, // já com quebra

                            type: 'line',
                            data: [{
                                x: minX,
                                y: s.avg
                            }, {
                                x: maxX,
                                y: s.avg
                            }],
                            parsing: false,
                            borderColor: color.line,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 0,
                        });
                    }

                    // Marcadores só se forem números válidos
                    if (typeof s.startDay === 'number' && isFinite(s.startDay)) markers.push({
                        x: s.startDay,
                        color: color.pt
                    });
                    if (typeof s.endDay === 'number' && isFinite(s.endDay)) markers.push({
                        x: s.endDay,
                        color: color.pt
                    });
                });

                window._toxCharts[elId] = new Chart(el, {
                    plugins: [verticalLines],
                    data: {
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // respeita a altura do contêiner (Tailwind)
                        animation: false,
                        scales: {
                            x: {
                                type: 'linear',
                                min: minX,
                                max: maxX,
                                title: {
                                    display: true,
                                    text: 'Data (linha do tempo)'
                                },
                                ticks: {
                                    precision: 0,
                                    callback: v => fmt(addDays(globalStart, Number(v)))
                                },
                                grid: {
                                    drawTicks: true
                                }
                            },
                            y: {
                                min: 0,
                                max: 100,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Toxicidade (%)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    title: (items) => {
                                        const d = items[0]?.raw?.x ?? null;
                                        return d != null ? fmt(addDays(globalStart, Number(d))) : '';
                                    },
                                    label: (ctx) => {
                                        const d = ctx.raw || {};
                                        return `${Number(d.y).toFixed(1)}%` + (d.label ? ` — ${d.label}` :
                                            '');
                                    }
                                }
                            },
                            verticalLines: {
                                markers
                            }
                        }
                    }
                });
            }

            // Boot: usa $chart do Blade, se houver
            function boot() {
                try {
                    const chartData = @json($chart ?? null);
                    if (chartData && chartData.series && Object.keys(chartData.series).length) {
                        renderChart(chartData);
                    }
                } catch (e) {
                    console.warn('Sem $chart ou JSON inválido', e);
                }
            }


            // Eventos que interessam ao Livewire/DOM
            document.addEventListener('DOMContentLoaded', boot);


            // >>> NOVO: escuta o evento do Livewire
            window.addEventListener('t4-chart-updated', (event) => {
                const chartData = event.detail?.chart ?? null;
                console.log('t4-chart-updated recebido:', chartData);

                if (chartData && chartData.series && Object.keys(chartData.series).length) {
                    renderChart(chartData);
                } else {
                    console.warn('t4-chart-updated veio sem séries', chartData);
                }
            });


        })();
    </script>
@endpush
