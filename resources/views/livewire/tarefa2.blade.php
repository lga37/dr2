<div>

    <x-slot name="header">
        <div x-data="{
            open: JSON.parse(localStorage.getItem('tarefa2_header_open') ?? 'true')
        }" x-init="$watch('open', v => localStorage.setItem('tarefa2_header_open', JSON.stringify(v)))" class="relative">
            <!-- Barra do título + botão -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Tarefa2 - Polarização do conteúdo (UGC)') }}
                </h2>

                <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                    :aria-expanded="open" aria-controls="t2-instrucoes">
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
            <div id="t2-instrucoes" x-show="open" x-transition.opacity.scale.origin.top x-cloak
                class="bg-white shadow-sm rounded-2xl p-6 md:p-8 border">

                <!-- INICIO -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-purple-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 12h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <circle cx="6" cy="12" r="2" fill="currentColor" />
                            <circle cx="18" cy="12" r="2" fill="currentColor" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Como medimos</h3>
                            <p class="text-slate-600 text-sm">
                                Aplicamos um serviço de <strong>análise de sentimento</strong> aos textos de
                                <em>título + descrição</em> de cada vídeo. O resultado vai de <code>–1</code> a
                                <code>+1</code>
                                e é convertido para <strong>–100% a +100%</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-amber-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="4" rx="1.5" stroke="currentColor"
                                stroke-width="1.5" />
                            <rect x="3" y="10" width="18" height="4" rx="1.5" stroke="currentColor"
                                stroke-width="1.5" />
                            <rect x="3" y="15" width="18" height="4" rx="1.5" stroke="currentColor"
                                stroke-width="1.5" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Como coletamos os vídeos</h3>
                            <p class="text-slate-600 text-sm">
                                Para otimizar chamadas à API, coletamos no máximo <strong>~500 vídeos</strong> por
                                canal.
                                Se o canal tiver muitos vídeos, dividimos a linha do tempo em até <strong>10
                                    janelas</strong>
                                e pegamos até <strong>50 vídeos por janela</strong> (amostragem temporal).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que você faz</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Selecione <strong>2 a 3 canais</strong> para comparar.</li>
                            <li>Baseando-se nos metadados exibidos, indique <strong>qual canal é mais
                                    polarizado</strong>
                                (positivo ou negativo — vale o <em>valor absoluto</em>).</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que nós calculamos</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Polarização de <em>títulos</em> e <em>descrições</em> de cada vídeo.</li>
                            <li>Média de polarização do canal (linha horizontal no gráfico).</li>
                            <li>Gráfico temporal com os pontos por vídeo, permitindo filtrar por “título” e “descrição”.
                            </li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">Quando termina</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Exibimos a <strong>média</strong> de cada canal e quem teve o <strong>maior
                                    |score|</strong>.</li>
                            <li>Você confirma sua hipótese e deixa um <strong>feedback</strong> rápido.</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-sky-50 border border-sky-100 text-sky-900 text-sm">
                    <span class="font-semibold">Nota metodológica:</span>
                    a coleta é feita por janelas no tempo (até 10) com limite de 50 vídeos por janela. Isso fornece
                    uma visão representativa da produção do canal, equilibrando custo de API e cobertura histórica.
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
                    clear="clearSelecionados" evaluate="validarTarefa2" :min="2" :max="3" />
                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />
                <x-results-table variant="canal" :items="$this->buscas" :selected="array_keys($selecionados ?? [])" />
            </div>


            {{-- B L O C O   D E   A V A L I A Ç Ã O --}}
            @if ($mostrarAvaliacao)
                <div id="avaliacao" class="mt-8 px-4">
                    <h3 class="text-lg font-semibold mb-3">Avaliação</h3>
                    @php
                        $cols = min(4, max(1, count($selecionados)));
                    @endphp
                    {{-- “Bleed” para ficar do tamanho da tabela abaixo --}}
                    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                        <div class="grid gap-6 auto-rows-fr"
                            style="grid-template-columns: repeat({{ $cols }}, minmax(0,1fr));">
                            @foreach ($selecionados as $id => $v)
                                @php @endphp
                                <article wire:key="{{ $id }}"
                                    class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white ring-2 ring-indigo-500">
                                    <x-cardcanal :v="$v" />
                                </article>
                            @endforeach
                        </div>
                    </div>


                    <div class="overflow-x-auto mt-8">
                        <table
                            class="divide-y divide-gray-200 divide-solid table-auto min-w-full text-sm tracking-tight leading-tight">
                            <thead>
                                <tr class="bg-gray-100 text-xs text-gray-700 text-center">
                                    @php
                                        #$videosSessao = session('t2_videos', []);
                                        $videosSessao = $videos_dos_canais;

                                        #dd($videosSessao);
                                        $numVids = max(count($videosSessao), 1); // evita /0
                                        $colWidth = number_format(100 / ($numVids * 7), 2);
                                    @endphp
                                    @foreach ($videosSessao as $canal_id => $dados)
                                        <th colspan="7" style="width: {{ $colWidth * 7 }}%;"
                                            class="border border-gray-300 px-2 py-4">
                                            <x-linkcanal :canalId="$canal_id" :titulo="$selecionados[$canal_id]['channelTitle'] ?? ''" />
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $max = collect($videosSessao)->map(fn($d) => count($d))->max() ?? 0;
                                @endphp

                                @for ($i = 0; $i < $max; $i++)
                                    @if ($i == 0)
                                        <tr
                                            class="border border-gray-300 w-[10px] font-bold text-center text-indigo-800 text-[10px] ">
                                            <td>#</td>
                                            <td>Titulo</td>
                                            <td>Likes</td>
                                            <td>Views</td>
                                            <td>Comments</td>
                                            <td>Data</td>
                                            <td>NLP</td>
                                        </tr>
                                    @endif

                                    <tr class="">
                                        @foreach ($videosSessao as $loopIndex => $dados)
                                            @php
                                                $c = $dados[$i] ?? null;
                                            @endphp
                                            @if ($c)
                                                <td
                                                    class="border border-gray-300 w-[10px] text-left text-gray-800 text-[10px] ">
                                                    {{ $i + 1 }}</td>
                                                <td
                                                    class="border text-xs border-gray-300 w-[420px] max-w-[420px] truncate break-all">
                                                    <a href="{{ $c['videoId'] }}" target="_blank">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($c['videoTitle'] ?? '[---]'), 120) }}
                                                    </a>
                                                </td>

                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoLikeCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoViewCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoCommentCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[20px] text-gray-800 text-[10px]">
                                                    {{ isset($c['videoDt']) ? \Carbon\Carbon::parse($c['videoDt'])->format('d/m/Y') : '--' }}
                                                </td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ isset($c['nlp1']) ? number_format($c['nlp1'] * 100, 1) . '%' : 'X' }}
                                                </td>
                                            @else
                                                <td colspan="7"
                                                    class="border border-gray-300 w-full text-gray-900 text-center italic text-[11px]">
                                                    --</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>


                            <tfoot>
                                <tr
                                    class="bg-gray-50 border-t border-gray-300 text-[11px] text-gray-700 font-semibold text-center">
                                    @foreach ($polarizMediaArray as $video_id => $polarizMedia)
                                        <td colspan="7" class="border py-3 text-5xl bg-indigo-50 text-indigo-900">
                                            Polariz. média (titulo):
                                            <span class="font-bold text-5xl">
                                                {{ $polarizMedia ? number_format($polarizMedia * 100, 1) . '%' : 'n/a' }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>

                        <!-- feedback -->
                        <div class="rounded-lg p-4 ring-4 w-full max-w-6xl mx-auto my-4 bg-green-50  ring-green-300">
                            <div class="mt-4 grid gap-2">
                                <label class="text-sm font-medium text-gray-700">
                                    Deixe um breve feedback: por que você escolheu esse canal?
                                </label>
                                <textarea rows="3" wire:model.defer="feedback"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: Pelo nome e engajamento (views, data de criação, tags, etc.) , achei que este canal seria mais polarizado positivamente, ou negativamente."></textarea>

                                <div class="flex items-center gap-3">
                                    <x-primary-button wire:click="salvarFeedback" wire:loading.attr="disabled"
                                        wire:target="salvarFeedback">
                                        Enviar feedback
                                    </x-primary-button>
                                    <div class="invisible" wire:loading.class.remove="invisible"
                                        wire:target="salvarFeedback">
                                        <span class="text-sm text-gray-500">Salvando…</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif


        </div>
    </div>



    <div class="mx-auto p-6 w-full max-w-[1400px]">
        <h2 class="text-xl font-semibold mb-2">Polarização (-100..+100) no tempo real</h2>

        {{-- filtros globais de tipo --}}
        <div class="flex items-center gap-4 text-sm mb-2">
            <label><input id="polOnlyTitle" type="checkbox" checked> Títulos</label>
            <label><input id="polOnlyDesc" type="checkbox" checked> Descrições</label>
            <label><input id="polOnlyAvg" type="checkbox" checked> Médias</label>
        </div>

        {{-- legenda HTML com link para o canal --}}
        <div id="polLegend" class="flex flex-wrap gap-4 items-center mb-3"></div>

        <div class="w-full" style="height: 420px;"> {{-- ajuste a altura aqui --}}
            <canvas id="polChart"></canvas>
        </div>
    </div>



</div>

@push('scripts')
    <script>
        (function() {
            // guarda instância p/ não criar gráfico duplicado
            if (!window._polCharts) window._polCharts = {};

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
                }, // extra
                {
                    pt: '#8b5cf6',
                    line: '#5b21b6'
                }, // extra
            ];

            const fmt = (d) => {
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yy = d.getFullYear();
                return `${dd}/${mm}/${yy}`;
            };

            const addDays = (d, n) => {
                const x = new Date(d);
                x.setDate(x.getDate() + n);
                return x;
            };

            // plugin das linhas verticais
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
                        ctx.globalAlpha = .5;
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

            function renderPolChart(payload, elId = 'polChart', attempts = 0) {
                const el = document.getElementById(elId);
                const legendHost = document.getElementById('polLegend');
                const cbTitle = document.getElementById('polOnlyTitle');
                const cbDesc = document.getElementById('polOnlyDesc');
                const cbAvg = document.getElementById('polOnlyAvg');

                if (!el) return;

                // Chart.js ainda não carregou? tenta de novo
                if (!window.Chart) {
                    if (attempts > 20) return; // evita loop infinito
                    return setTimeout(() => renderPolChart(payload, elId, attempts + 1), 150);
                }

                if (!payload || !payload.series || !Object.keys(payload.series).length) {
                    console.warn('t2: payload vazio ou inválido:', payload);
                    return;
                }

                console.log('t2: renderPolChart()', payload);

                const globalStart = new Date(payload.globalStart);
                const minX = payload.min ?? 0;
                const maxX = payload.max ?? 0;

                // destrói gráfico antigo
                if (window._polCharts[elId]) {
                    try {
                        window._polCharts[elId].destroy();
                    } catch (_) {}
                    delete window._polCharts[elId];
                }
                if (legendHost) legendHost.innerHTML = '';

                const datasets = [];
                const markers = [];
                const vids = Object.keys(payload.series);

                vids.forEach((channelId, idx) => {
                    const s = payload.series[channelId] || {};
                    const color = palette[idx % palette.length];
                    const labelBase = s.title || channelId;

                    // pontos de título
                    datasets.push({
                        label: labelBase + ' — título',
                        type: 'scatter',
                        data: Array.isArray(s.points_title) ? s.points_title : [],
                        parsing: false,
                        showLine: false,
                        pointStyle: 'circle',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        backgroundColor: color.pt,
                        borderColor: color.pt,
                        channelId,
                        metaTipo: 'title',
                    });

                    // pontos de descrição
                    datasets.push({
                        label: labelBase + ' — descrição',
                        type: 'scatter',
                        data: Array.isArray(s.points_desc) ? s.points_desc : [],
                        parsing: false,
                        showLine: false,
                        pointStyle: 'crossRot',
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        backgroundColor: color.pt,
                        borderColor: color.pt,
                        channelId,
                        metaTipo: 'desc',
                    });

                    // linha da média
                    if (typeof s.avg === 'number' && isFinite(s.avg)) {
                        datasets.push({
                            label: labelBase + ' — média',
                            type: 'line',
                            data: [{
                                    x: minX,
                                    y: s.avg
                                },
                                {
                                    x: maxX,
                                    y: s.avg
                                },
                            ],
                            parsing: false,
                            borderColor: color.line,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            channelId,
                            metaTipo: 'avg',
                        });
                    }

                    if (typeof s.startDay === 'number' && isFinite(s.startDay))
                        markers.push({
                            x: s.startDay,
                            color: color.pt
                        });
                    if (typeof s.endDay === 'number' && isFinite(s.endDay))
                        markers.push({
                            x: s.endDay,
                            color: color.pt
                        });
                });

                const chart = new Chart(el, {
                    plugins: [verticalLines],
                    data: {
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
                                    callback: v => fmt(addDays(globalStart, Number(v))),
                                },
                            },
                            y: {
                                min: -100,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Polarização (-100 .. +100)'
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    title(items) {
                                        const day = items[0]?.raw?.x ?? null;
                                        return day != null ?
                                            fmt(addDays(globalStart, Number(day))) :
                                            '';
                                    },
                                    label(ctx) {
                                        const r = ctx.raw || {};
                                        return ` ${ctx.dataset.label}: ${Number(r.y).toFixed(1)}` +
                                            (r.label ? ' — ' + r.label : '');
                                    },
                                },
                            },
                            verticalLines: {
                                markers
                            },
                        },
                    },
                });

                window._polCharts[elId] = chart;

                // legenda HTML
                if (legendHost) {
                    const byChannel = {};
                    chart.data.datasets.forEach((ds, i) => {
                        const s = payload.series[ds.channelId] || {};
                        const title = s.title || ds.label?.split(' — ')[0] || ds.channelId;

                        if (!byChannel[ds.channelId]) {
                            byChannel[ds.channelId] = {
                                idxs: [],
                                title
                            };
                        }
                        byChannel[ds.channelId].idxs.push(i);
                    });

                    Object.keys(byChannel).forEach((chId, n) => {
                        const info = byChannel[chId];
                        const color = palette[n % palette.length].pt;

                        const pill = document.createElement('a');
                        pill.href = `https://www.youtube.com/channel/${chId}`;
                        pill.target = '_blank';
                        pill.rel = 'noopener';
                        pill.className = 'px-3 py-1 rounded-full text-sm inline-block select-none';
                        pill.style.border = `1px solid ${color}`;
                        pill.style.color = color;
                        pill.textContent = info.title.length > 40 ?
                            info.title.slice(0, 40) + '…' :
                            info.title;
                        pill.title = info.title;

                        pill.addEventListener('click', (e) => {
                            if (e.ctrlKey || e.metaKey || e.button === 1) return;
                            e.preventDefault();
                            const anyHidden = info.idxs.some(i => !chart.isDatasetVisible(i));
                            info.idxs.forEach(i => chart.setDatasetVisibility(i, anyHidden));
                            chart.update();
                        });

                        const wrap = document.createElement('div');
                        wrap.className = 'flex items-center';
                        wrap.appendChild(pill);
                        legendHost.appendChild(wrap);
                    });
                }

                // filtros globais
                function applyFilters() {
                    chart.data.datasets.forEach((ds, i) => {
                        const ok =
                            (ds.metaTipo === 'title' && cbTitle?.checked) ||
                            (ds.metaTipo === 'desc' && cbDesc?.checked) ||
                            (ds.metaTipo === 'avg' && cbAvg?.checked);

                        chart.setDatasetVisibility(i, ok);
                    });
                    chart.update();
                }

                if (cbTitle && cbDesc && cbAvg) {
                    [cbTitle, cbDesc, cbAvg].forEach(cb => cb.addEventListener('change', applyFilters));
                    applyFilters();
                }
            }

            // Boot inicial – usa $chart se já vier preenchido
            function boot() {
                try {
                    const initial = @json($chart ?? null);
                    console.log('t2 boot initial chart:', initial);
                    if (initial && initial.series && Object.keys(initial.series).length) {
                        renderPolChart(initial);
                    }
                } catch (e) {
                    console.warn('t2: erro ao parsear $chart inicial', e);
                }
            }

            document.addEventListener('DOMContentLoaded', boot);

           

           

            // Evento vindo do Livewire (v3)
            Livewire.on('t2-chart-updated', (payload) => {
                console.log('t2-chart-updated recebido:', payload);
                // aqui a gente passa só o objeto do gráfico:
                renderPolChart(payload.chart);
            });


        })();
    </script>
@endpush
