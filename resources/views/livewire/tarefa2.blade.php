<div>

    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-purple-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 12h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <circle cx="6" cy="12" r="2" fill="currentColor" />
                    <circle cx="18" cy="12" r="2" fill="currentColor" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    WIDGET 2 — Toxicidade e <span class="text-purple-700">Monetização</span>
                </h2>
            </div>
        </div>

{{-- bloco resumido --}}
<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- explicação --}}
    <div class="lg:col-span-2 rounded-2xl border border-purple-100 bg-gradient-to-br from-purple-50 to-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-purple-800 mb-3 uppercase tracking-wide">
            Como utilizar o Widget 2
        </h3>

        <div class="space-y-2 text-sm text-slate-700 leading-6">
            <p>
                Pesquise canais relacionados a um mesmo tema,
                selecione de <strong>2 a 3 canais</strong>
                e clique em <strong>avaliar canais</strong>.
            </p>

            <p>
                O sistema divide os vídeos em <strong>buckets temporais</strong>,
                analisando a evolução da <strong>toxicidade dos comentários</strong>
                e dos indicadores de <strong>monetização</strong> ao longo do tempo.
            </p>

            <p>
                Também são exibidos indícios de monetização
                <strong>off-platform</strong>, como URLs externas,
                além de estimativas públicas do VidIQ.
            </p>
        </div>
    </div>

    {{-- legenda --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 uppercase tracking-wide">
            Legenda
        </h3>

        <div class="space-y-2 text-sm">

            <div class="flex items-center justify-between rounded-lg border border-purple-100 bg-purple-50 px-3 py-2">
                <span class="text-slate-600">Bucket</span>
                <span class="font-semibold text-purple-700">Período temporal</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-rose-100 bg-rose-50 px-3 py-2">
                <span class="text-slate-600">Tox. média</span>
                <span class="font-semibold text-rose-700">Comentários</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-3 py-2">
                <span class="text-slate-600">URLs/vídeo</span>
                <span class="font-semibold text-amber-700">Monetização externa (proxy)</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                <span class="text-slate-600">VidIQ/mês</span>
                <span class="font-semibold text-emerald-700">Estimativa pública</span>
            </div>

        </div>
    </div>

</div>

    </div>



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

                        <!-- feedback -->
                        <div class="rounded-lg p-4 ring-4 w-full max-w-6xl mx-auto my-4 bg-green-50  ring-green-300">
                            <div class="mt-4 grid gap-2">
                                <label class="text-sm font-medium text-gray-700">
                                    Deixe um breve feedback: por que você escolheu esse canal?
                                </label>
                                <textarea rows="3" wire:model.defer="feedback"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: Pelo nome e engajamento (views, data de criação, tags, etc.) , achei que este canal seria mais 
                                    toxico, etc ..."></textarea>

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

    @php
        #$pm = session('pm_result', []);
        $mtResult = $this->mtResult;
    @endphp
    @if (!empty($mtResult))
        <div class="mx-auto max-w-[1500px] p-6">
            <h2 class="text-xl font-semibold mb-4">
                Widget M–T — Monetização e Toxicidade por buckets temporais
            </h2>

            @foreach ($mtResult as $channelId => $row)
                @php
                    $isGreen = ($row['cor'] ?? '') === 'green';

                    $border = $isGreen ? 'border-green-500' : 'border-red-500';
                    $bg = $isGreen ? 'bg-green-50' : 'bg-red-50';
                    $text = $isGreen ? 'text-green-800' : 'text-red-800';

                    $vidiqUrl = 'https://vidiq.com/youtube-stats/channel/' . $channelId . '/';
                @endphp

                <div class="mb-8 rounded-2xl border-2 {{ $border }} {{ $bg }} p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>

                            <x-linkcanal :canalId="$channelId" :titulo="$row['channel']['channelTitle'] ?? $channelId" />

                        </div>

                        <div class="text-right text-sm">
                            <div><strong>Tox. canal:</strong>
                                {{ isset($row['tox_canal']['media']) ? number_format($row['tox_canal']['media'] * 100, 2, ',', '.') . '%' : '-' }}
                            </div>
                            <div><strong>Comentários analisados:</strong>
                                {{ $row['tox_canal']['n'] ?? 0 }}
                            </div>
                            <div><strong>URLs externas:</strong>
                                {{ $row['monetizacao_canal']['external_urls_count'] ?? 0 }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                        @foreach ($row['buckets'] as $bucket)
                            @php
                                $a = $bucket['analysis'];
                                $t = $a['toxicity'];
                                $m = $a['monetizacao_off_platform'];
                            @endphp

                            <div class="rounded-xl bg-white border p-3 text-xs shadow-sm">
                                <div class="font-bold {{ $text }}">
                                    Bucket {{ $bucket['idx'] }}
                                </div>

                                <div class="text-slate-500 mb-2">
                                    {{ $bucket['label'] }}
                                </div>

                                <div><strong>Vídeos:</strong> {{ $a['videos_count'] }}</div>
                                <div><strong>Comentários:</strong> {{ $t['n'] ?? 0 }}</div>
                                <div><strong>Tox. média:</strong>
                                    {{ isset($t['media']) ? number_format($t['media'] * 100, 2, ',', '.') . '%' : '-' }}
                                </div>
                                <div><strong>Tox. máx:</strong>
                                    {{ isset($t['max']) ? number_format($t['max'] * 100, 2, ',', '.') . '%' : '-' }}
                                </div>
                                <div><strong>Alta tox.:</strong>
                                    {{ isset($t['alta_taxa']) ? number_format($t['alta_taxa'] * 100, 1, ',', '.') . '%' : '-' }}
                                </div>
                                <div><strong>URLs/vídeo:</strong> {{ $m['urls_media_por_video'] ?? 0 }}</div>

                                <div class="mt-3 border-t pt-2">
                                    <strong>Comentários amostra</strong>

                                    @foreach ($a['comentarios_sample'] ?? [] as $c)
                                        <div class="mt-2 p-2 rounded bg-slate-50 border">
                                            <div class="text-slate-500">
                                                tox:
                                                {{ isset($c['tox']) ? number_format($c['tox'] * 100, 1, ',', '.') . '%' : '-' }}
                                            </div>
                                            <div>
                                                {{ \Illuminate\Support\Str::limit($c['texto'] ?? ($c['text'] ?? ''), 80) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="rounded-xl border-2 {{ $border }} p-3 text-xs shadow-sm relative overflow-hidden"
                            style="
                            background-image: repeating-linear-gradient(
                                135deg,
                                rgba(255,255,255,0.95) 0px,
                                rgba(255,255,255,0.95) 8px,
                                rgba(0,0,0,0.035) 8px,
                                rgba(0,0,0,0.035) 16px
                            );
                        ">
                            <div
                                class="absolute top-0 left-0 right-0 h-1 {{ $isGreen ? 'bg-green-500' : 'bg-red-500' }}">
                            </div>

                            <div class="font-bold {{ $text }} mb-3">
                                Monetização
                            </div>

                            <div class="mt-2">
                                <strong>VidIQ/mês:</strong><br>
                                US$
                                {{ number_format($row['monetizacao_canal']['vidiq_monthly_avg_usd'] ?? 0, 0, ',', '.') }}
                            </div>

                            <div class="mt-2">
                                <strong>Off-platform:</strong><br>
                                {{ $row['monetizacao_canal']['external_urls_count'] ?? 0 }} URLs
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-200">
                                <a href="{{ $vidiqUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-md {{ $isGreen ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }} font-semibold">
                                    Conferir no VidIQ ↗
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif


    {{-- <div class="mx-auto p-6 w-full max-w-[1400px]">
        <h2 class="text-xl font-semibold mb-2">Toxicidade (0 .. 100%) no tempo real</h2>
        <div class="flex items-center gap-4 text-sm mb-2">
            <label><input id="polOnlyTitle" type="checkbox" checked> Títulos</label>
            <label><input id="polOnlyAvg" type="checkbox" checked> Médias</label>
        </div>
        <div id="polLegend" class="flex flex-wrap gap-4 items-center mb-3"></div>
        <div class="w-full" style="height: 420px;">
            <canvas id="polChart"></canvas>
        </div>
    </div> --}}



</div>
{{-- 
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
                if (legendHost)
                    legendHost.innerHTML = '';

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
                    // if (typeof s.avg === 'number' && isFinite(s.avg)) {
                    //     datasets.push({
                    //         label: labelBase + ' — média',
                    //         type: 'line',
                    //         data: [{
                    //                 x: minX,
                    //                 y: s.avg
                    //             },
                    //             {
                    //                 x: maxX,
                    //                 y: s.avg
                    //             },
                    //         ],
                    //         parsing: false,
                    //         borderColor: color.line,
                    //         borderWidth: 2,
                    //         borderDash: [6, 4],
                    //         pointRadius: 0,
                    //         channelId,
                    //         metaTipo: 'avg',
                    //     });
                    // }

                    // linha da média
                    let avgRaw = Number(s.avg);

                    // se vier em escala 0..1, converte pra 0..100
                    if (!Number.isNaN(avgRaw) && isFinite(avgRaw)) {
                        //const avgVal = (avgRaw >= -1 && avgRaw <= 1) ? avgRaw * 100 : avgRaw;
                        const avgVal = avgRaw;

                        datasets.push({
                            label: labelBase + ' — média',
                            type: 'line',
                            data: [{
                                    x: minX,
                                    y: avgVal
                                },
                                {
                                    x: maxX,
                                    y: avgVal
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

                        console.log('Média canal', channelId, 'raw:', avgRaw, 'chart:', avgVal);
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
                                min: 0,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Toxicidade (0 .. 100)'
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
@endpush --}}
