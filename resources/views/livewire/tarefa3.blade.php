<div>

    <x-slot name="header">
        <div x-data="{
            open: JSON.parse(localStorage.getItem('tarefa3_header_open') ?? 'true')
        }" x-init="$watch('open', v => localStorage.setItem('tarefa3_header_open', JSON.stringify(v)))" class="relative">
            <!-- Barra do título + botão -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Tarefa3 - Monetização') }}
                </h2>

                <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                    :aria-expanded="open" aria-controls="t3-instrucoes">
                    <svg x-show="!open" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" x-cloak>
                        <path d="M10 6l6 6H4l6-6z" />
                    </svg>
                    <svg x-show="open" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" x-cloak>
                        <path d="M10 14L4 8h12l-6 6z" />
                    </svg>
                    <span x-show="open" x-cloak>Ocultar instruções</span>
                    <span x-show="!open" x-cloak>Mostrar instruções</span>
                </button>
            </div>

            <!-- Bloco dobrável -->
            <div id="t3-instrucoes" x-show="open" x-transition.opacity.scale.origin.top x-cloak
                class="bg-white shadow-sm rounded-2xl p-6 md:p-8 border">

                <!-- INICIO -->
                <div class="flex items-start gap-4">
                    <!-- ícone $ -->
                    <svg class="w-12 h-12 shrink-0 text-emerald-600" viewBox="0 0 48 48" fill="none"
                        aria-hidden="true">
                        <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2" />
                        <!-- “R$” como texto (usa a fonte do sistema) -->
                        <text x="24" y="28" text-anchor="middle"
                            font-family="system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif"
                            font-weight="700" font-size="16" fill="currentColor">
                            R$
                        </text>
                    </svg>

                    <div>
                        <h2 class="text-2xl md:text-3xl font-semibold leading-tight">
                            Tarefa 3 — Evolução de <span class="text-emerald-700">engajamento</span> e <span
                                class="text-emerald-700">rentabilidade</span> por canal
                        </h2>
                        <p class="mt-1 text-slate-600">
                            Você comparará <strong>2 canais</strong> e decidirá qual deles é <strong>mais
                                rentável</strong>
                            — não pelo total acumulado,
                            mas pela <strong>eficiência</strong> da produção: <em>quanto o conteúdo publicado rende
                                por
                                minuto</em>.
                        </p>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- como medimos -->
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="14" rx="3" stroke="currentColor"
                                stroke-width="1.5" />
                            <path d="M7 13.5l2.5-2.5L12 13l4-4 1 1-5 5-2.5-2.5L8 14.5l-1-1z" fill="currentColor" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Como estimamos a rentabilidade</h3>
                            <p class="text-slate-600 text-sm">
                                Integramos <strong>duas fontes públicas</strong> (por ex., SocialBlade e VidIQ) e
                                usamos a
                                <em>média</em> do
                                intervalo <strong>mín–máx</strong> de ganhos estimados (CPM/RPM) do período atual.
                                Combinamos isso com:
                                <strong>nº de vídeos</strong> e <strong>minutagem publicada</strong> para estimar
                                <em>R$/min
                                    publicado</em>.
                            </p>
                        </div>
                    </div>

                    <!-- o que será exibido -->
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-amber-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 18V7m5 11V5m5 13V9m5 9V4" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">O que você verá</h3>
                            <p class="text-slate-600 text-sm">
                                Gráficos com <strong>inscritos no tempo</strong> (com <em>POIs</em>: criação,
                                entrada no
                                YPP, 100k/1M etc.),
                                <strong>uploads e minutagem</strong> e a <strong>eficiência financeira</strong>
                                (ganho
                                estimado por minuto).
                                O foco é a <em>tendência linear</em> recente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que você faz</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Selecione <strong>2 canais</strong> para comparar.</li>
                            <li>Observe os metadados e os gráficos exibidos.</li>
                            <li>Indique <strong>qual é mais rentável</strong> em termos de <em>R$/min de
                                    conteúdo</em>.</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que nós calculamos</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li><strong>Estimativa de ganhos</strong> (média do intervalo mín–máx de duas fontes).
                            </li>
                            <li><strong>POIs</strong> na curva de inscritos: criação do canal, entrada no YPP,
                                placas (100k/1M/10M), etc.</li>
                            <li><strong>Uploads</strong>, <strong>minutagem total</strong> e <strong>R$/min
                                    publicado</strong>.</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">Assumimos (limitações)</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li><em>Crescimento linear</em> recente (WebArchive instável para séries completas).
                            </li>
                            <li>CPM/RPM de fontes públicas é <em>médio e enviesado ao mercado EUA</em>.</li>
                            <li>Ignoramos vídeos excluídos e monetizações externas (loja, patrocínios, membresias).
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-900 text-sm">
                    <span class="font-semibold">Sua decisão:</span>
                    considere os gráficos e a eficiência estimada (R$/min). Marque o canal com <strong>maior
                        eficiência</strong>
                    — o que transforma melhor a sua produção em receita, independentemente da idade do canal.
                </div>
                <!-- FIM -->

            </div>

        </div>
    </x-slot>

    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">


            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <x-selecionados-table :items="$selecionados" type="canal" remove="removeSelecionado"
                    clear="clearSelecionados" evaluate="avaliarCanais" :min="2" :max="3" />

                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />

                <x-results-table variant="canal" :items="$this->buscas" :selected="array_keys($selecionados ?? [])" />
            </div>


            <div class="grid grid-cols-1">

                @if ($mostrarAvaliacao)
                    <div id="avaliacao" class="mt-8 px-4">
                        <h3 class="text-lg font-semibold mb-3">Avaliação</h3>


                        <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                            <div class="grid gap-6 auto-rows-fr"
                                style="grid-template-columns: repeat(2, minmax(0,1fr));">

                                @foreach ($selecionados as $id => $v)
                                    @php
                                        #dump($v);
                                    @endphp
                                    <article wire:key="{{ $id }}"
                                        class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white
                                        {{ $maisEconomizado === $id ? 'ring-2 ring-indigo-500' : '' }}">

                                        {{-- card-canal --}}

                                        <x-cardcanal :v="$v" />
                                        {{-- <div class="flex items-start">
                                            <x-imagem :src="$v['channelThumb']" tipo="gde" class="shadow-sm" />
                                            <div class="flex-1 ">
                                                <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />

                                                <div class="h-20 text-xs text-justify text-gray-500 mt-1 line-clamp-4">
                                                    {{ $v['channelDesc'] ?? '' }}
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="text-lg h-8 font-semibold mt-4 mb-1">
                                            Dados do Canal
                                            <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />

                                            — criado em
                                            {{ isset($v['channelDt']) ? \Carbon\Carbon::parse($v['channelDt'])->format('d/m/Y') : '—' }}
                                        </h4>

                                        <x-keywords :items="$v['channelKeywords'] ?? []" limit="8" rows="2" />


                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                            <div class="bg-gray-50 p-2 rounded">
                                                <div class="text-gray-500">Origem/Pais</div>
                                                <div class="font-semibold">{{ $v['channelCountry'] ?? '-' }}</div>
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <div class="text-gray-500">Total Views</div>
                                                <div class="font-semibold">
                                                    {{ number_format($v['channelViews'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <div class="text-gray-500">Total Vídeos</div>
                                                <div class="font-semibold">
                                                    {{ number_format($v['channelVideos'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <div class="text-gray-500">Inscritos</div>
                                                <div class="font-semibold">
                                                    {{ number_format($v['channelSubs'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div> --}}

                                        <!-- fim cardcanal -->


                                        @php
                                            // URLs externas
                                            $url_vidiq = "https://vidiq.com/youtube-stats/channel/{$id}/";
                                            $url_socialblade = "https://socialblade.com/youtube/channel/{$id}";
                                        @endphp

                                        <div class="mt-3 rounded-xl border bg-white/60 p-3">
                                            <div class="flex flex-wrap items-center gap-4 md:gap-6">

                                                {{-- Monetização estimada --}}
                                                <div class="flex items-center gap-2 rounded-lg bg-zinc-50 px-3 py-2">
                                                    <span class="text-xs font-semibold text-zinc-500">Monetiz. Est.
                                                    </span>
                                                    <span
                                                        class="text-lg font-semibold text-green-700 md:text-xl">U$ {{ isset($v['monetAvgUsd']) ? $v['monetAvgUsd'].'.00 /mes': '' }}</span>
                                                </div>

                                                {{-- Dias monetizados (desde a data em que atingiu 5k) --}}
                                                <div class="flex items-center gap-2 rounded-lg bg-zinc-50 px-3 py-2">
                                                    <span class="text-xs font-semibold text-zinc-500">Dias
                                                        monetizados</span>
                                                    <span
                                                        class="text-lg font-semibold md:text-xl">{{ $v['diasMonetizados'] ?? '' }}</span>
                                                    @isset($dt5000)
                                                        <span class="text-[11px] text-zinc-500">desde
                                                            {{ \Carbon\Carbon::parse($dt5000)->format('d/m/Y') }}</span>
                                                    @endisset
                                                </div>

                                                {{-- Minutagem total de vídeos --}}
                                                <div class="flex items-center gap-2 rounded-lg bg-zinc-50 px-3 py-2">
                                                    <span class="text-xs font-semibold text-zinc-500">Minutagem
                                                        total</span>
                                                    <span
                                                        class="text-lg font-semibold md:text-xl">{{ $v['minutagemTotalFmt'] ?? '' }}</span> - 
                                                    <span
                                                        class="text-lg font-semibold md:text-xl">{{ $v['minutagemTotal'] ?? '' }}</span>


                                                    {{-- ex.: "123 h 45 min" ou "7.430 min" --}}
                                                </div>

                                                {{-- Ações/Links --}}
                                                <div class="ml-auto flex items-center gap-2">
                                                    {{-- vidIQ (ciano/azul) --}}
                                                    <a href="{{ $url_vidiq }}" target="_blank" rel="noopener"
                                                        class="group inline-flex items-center gap-2 rounded-lg border border-cyan-600 bg-cyan-50
            px-3 py-2 text-sm font-medium text-cyan-700 shadow-sm transition
            hover:bg-cyan-100 hover:border-cyan-700 hover:text-cyan-800
            focus:outline-none focus:ring-2 focus:ring-cyan-300">
                                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                                                            viewBox="0 0 24 24" fill="currentColor"
                                                            aria-hidden="true">
                                                            <path d="M3 12l7-9 4 6 7-3-7 15-4-6-7 3z" />
                                                        </svg>
                                                        vidIQ
                                                    </a>

                                                    {{-- SocialBlade (vermelho) --}}
                                                    <a href="{{ $url_socialblade }}" target="_blank" rel="noopener"
                                                        class="group inline-flex items-center gap-2 rounded-lg border border-rose-600 bg-rose-50
            px-3 py-2 text-sm font-medium text-rose-700 shadow-sm transition
            hover:bg-rose-100 hover:border-rose-700 hover:text-rose-800
            focus:outline-none focus:ring-2 focus:ring-rose-300">
                                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                                                            viewBox="0 0 24 24" fill="currentColor"
                                                            aria-hidden="true">
                                                            <path d="M3 13h6l3-6 3 10 3-6h3v2h-2l-4 8-3-10-3 6H3z" />
                                                        </svg>
                                                        SocialBlade
                                                    </a>
                                                </div>


                                            </div>
                                        </div>





                                        <div class="mt-auto pt-4">

                                            <div class="mt-4" wire:ignore>
                                                <canvas id="chart-{{ $v['channelId'] }}" class="h-96 w-full"
                                                    data-titulo="Inscritos — {{ $v['channelTitle'] }}"
                                                    data-data="{{ \Carbon\Carbon::parse($v['channelDt'])->format('Y-m-d') }}"
                                                    data-inscritos="{{ $v['channelSubs'] ?? 0 }}">
                                                </canvas>
                                            </div>

                                            <div class="pt-4">
                                                <x-secondary-button
                                                    wire:click="escolherMaisEconomizado('{{ $id }}')"
                                                    :disabled="$maisEconomizado === $id">
                                                    Marcar como mais Economizado
                                                </x-secondary-button>
                                                @if ($maisEconomizado === $id)
                                                    <span
                                                        class="ml-3 text-indigo-600 text-sm font-semibold">Selecionado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach

                            </div>
                        </div>


                        <x-primary-button class="w-full text-6xl p-10 mt-6 text-center " wire:click="validarTarefa3"
                            wire:loading.attr="disabled" wire:target="validarTarefa3">
                            Finalizar Avaliação de Monetizaçao

                            <span class="invisible" wire:loading.class.remove="invisible"
                                wire:target="validarTarefa2">
                                <span class="text-sm text-yellow-500">Aguarde Processando ...</span>
                            </span>
                        </x-primary-button>


                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) return;
            const annotationPlugin = window['chartjs-plugin-annotation'];
            if (annotationPlugin) Chart.register(annotationPlugin);

            // === HELPERS ===
            const toDate = v => (v instanceof Date ? v : new Date(v));
            const addMonths = (d, m) => {
                const x = new Date(d);
                x.setMonth(x.getMonth() + m);
                return x;
            };
            const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
            const dateAtY = (y, x0, y0, x1, y1) => {
                const f = clamp((y - y0) / (y1 - y0), 0, 1);
                const t = x0.getTime() + f * (x1.getTime() - x0.getTime());
                return new Date(t);
            };
            const niceMaxY = raw => {
                const pow = Math.pow(10, Math.floor(Math.log10(raw)));
                for (const k of [1, 2, 2.5, 5, 10]) {
                    const c = k * pow;
                    if (c >= raw) return c;
                }
                return 10 * pow;
            };

            const fmtNum = new Intl.NumberFormat('pt-BR');

            // === FUNÇÃO BASE (usa o teu mesmo código) ===
            function makeChart(elId, titulo, dataInicio, inscritosFinal) {
                const el = document.getElementById(elId);
                if (!el) return;

                // --- parâmetros dinâmicos ---
                const x0 = toDate(dataInicio);
                const x1 = new Date(); // hoje
                const y0 = 0;
                const y1 = Number(inscritosFinal || 0);



                const fmt = (n) => Number(n).toLocaleString('pt-BR');
                const POI_ABS = [5_000, 100_000];
                const POIS = POI_ABS.map(v => ({
                    y: v,
                    name: `POI ${fmt(v)}`
                }));


                const mainLine = [{
                        x: x0.toISOString(),
                        y: y0
                    },
                    {
                        x: x1.toISOString(),
                        y: y1
                    }
                ];


                //========================================
                const poiData = POIS.map(p => {
                    const d = (y1 > 0) ? dateAtY(p.y, x0, y0, x1, y1) : x0;
                    return {
                        ...p,
                        x: d,
                        xISO: d.toISOString()
                    };
                });

                const farthestPoiDate = poiData.reduce((acc, p) => p.x > acc ? p.x : acc, x1);
                const xMin = addMonths(x0, -3);
                const xMax = addMonths(farthestPoiDate, 1); // garanta espaço à direita


                const poiVerticalDatasets = poiData.map((p, idx) => ({
                    label: p.name,
                    data: [{
                            x: p.xISO,
                            y: 0
                        },
                        {
                            x: p.xISO,
                            y: p.y
                        }
                    ],
                    showLine: true,
                    fill: false,
                    borderDash: [6, 6],
                    borderWidth: 2,
                    borderColor: idx === 0 ? '#ef4444' : '#7c3aed',
                    pointRadius: 0,
                    order: 9
                }));



                const poiDotsDataset = {
                    label: 'POIs',
                    data: poiData.map(p => ({
                        x: p.xISO,
                        y: p.y,
                        name: p.name,
                        isFirst: p.y === POIS[0].y
                    })),
                    parsing: {
                        xAxisKey: 'x',
                        yAxisKey: 'y'
                    },
                    showLine: false,
                    pointRadius: ctx => ctx.raw?.isFirst ? 8 : 6,
                    pointHoverRadius: ctx => ctx.raw?.isFirst ? 11 : 9,
                    pointBackgroundColor: ctx => ctx.raw?.isFirst ? '#ef4444' : '#7c3aed',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    order: 20
                };





                const annotations = {};
                poiData.forEach((p, idx) => {
                    const color = idx === 0 ? '#ef4444' : '#7c3aed';
                    annotations[`v_${p.y}`] = {
                        type: 'line',
                        xMin: p.x,
                        xMax: p.x,
                        borderColor: color,
                        borderWidth: 2,
                        borderDash: [6, 6],
                        z: 15,
                        drawTime: 'afterDatasetsDraw'
                    };
                    annotations[`h_${p.y}`] = {
                        type: 'line',
                        yMin: p.y,
                        yMax: p.y,
                        borderColor: color,
                        borderWidth: 2,
                        borderDash: [6, 6],
                        z: 15,
                        drawTime: 'afterDatasetsDraw'
                    };
                    annotations[`pt_${p.y}`] = {
                        type: 'point',
                        xValue: p.x,
                        yValue: p.y,
                        radius: p.y === POIS[0].y ? 9 : 7,
                        backgroundColor: color,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        z: 20,
                        drawTime: 'afterDatasetsDraw',
                        label: {
                            display: true,
                            content: [p.name, `${fmtNum.format(p.y)} inscritos`],
                            position: 'top',
                            color: '#111827',
                            backgroundColor: 'rgba(255,255,255,0.95)',
                            padding: 6,
                            borderRadius: 6
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: () => `${p.name}: ${fmtNum.format(p.y)} inscritos`
                            }
                        }
                    };
                });

                const yMax = niceMaxY(y1 * 1.1);

                const config = {
                    type: 'line',
                    data: {
                        datasets: [{
                                label: titulo,
                                data: mainLine,
                                showLine: true,
                                fill: 'start',
                                spanGaps: true,
                                backgroundColor: 'rgba(14,165,233,0.12)',
                                borderColor: '#0ea5e9',
                                borderWidth: 2,
                                pointBackgroundColor: '#0ea5e9',
                                pointRadius: 4
                            },
                            ...poiVerticalDatasets,
                            poiDotsDataset
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'month',
                                    tooltipFormat: 'PP'
                                },
                                min: xMin.toISOString(),
                                max: xMax.toISOString(),
                                title: {
                                    display: true,
                                    text: 'Tempo'
                                },
                                grid: {
                                    display: true
                                }
                            },
                            y: {
                                beginAtZero: true,
                                min: 0,
                                max: yMax,
                                ticks: {
                                    callback: v => fmtNum.format(v)
                                },
                                title: {
                                    display: true,
                                    text: 'Inscritos'
                                },
                                grid: {
                                    display: true
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                intersect: true,
                                mode: 'nearest',
                                callbacks: {
                                    label: (ctx) => {
                                        const y = ctx.parsed?.y;
                                        const x = ctx.parsed?.x ? new Date(ctx.parsed.x) : null;
                                        const when = x ? x.toLocaleDateString('pt-BR') : '';
                                        const raw = ctx.raw || {};
                                        const prefix = raw?.name ? `${raw.name}: ` : '';
                                        return `${prefix}${fmtNum.format(y)} inscritos — ${when}`;
                                    }
                                }
                            },
                            annotation: {
                                annotations
                            }
                        },
                        elements: {
                            line: {
                                tension: 0
                            }
                        }
                    }
                };

                new Chart(el.getContext('2d'), config);
            }


            // const charts = document.querySelectorAll('canvas[id^="chart-"]');

            // charts.forEach(canvas => {
            //     const elId = canvas.id;
            //     const titulo = canvas.dataset.titulo;
            //     const dataInicio = canvas.dataset.data;
            //     const inscritos = Number(canvas.dataset.inscritos || 0);

            //     // Chama a tua função existente
            //     makeChart(elId, titulo, dataInicio, inscritos);
            // });





            // === Inicializa todos os canvases presentes na página ===
            function initCharts() {
                document.querySelectorAll('canvas[id^="chart-"]').forEach((canvas) => {
                    if (canvas.dataset.chartInit === '1') return; // evita duplicar
                    canvas.dataset.chartInit = '1';

                    const elId = canvas.id;
                    const titulo = canvas.dataset.titulo;
                    const dataInicio = canvas.dataset.data;
                    const inscritos = Number(canvas.dataset.inscritos || 0);
                    makeChart(elId, titulo, dataInicio, inscritos);
                });
            }

            // 1) na carga inicial
            initCharts();

            // 2) sempre que o Livewire re-renderizar algo
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('message.processed', () => {
                    initCharts();
                });
            });




        });
    </script>
@endpush
