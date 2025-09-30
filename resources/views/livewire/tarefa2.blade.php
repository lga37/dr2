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
                    clear="clearSelecionados" evaluate="avaliarCanais" :min="2" :max="3" />

                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais"
                    add-model="addInput" on-add="addCanalByInput" />

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
                                @php
                                    #dump($v);
                                @endphp
                                <article wire:key="{{ $id }}"
                                    class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white
                                     {{ $maisPolarizado === $id ? 'ring-2 ring-indigo-500' : '' }}">

                                    {{-- Cabeçalho --}}
                                    <div class="flex gap-3">
                                        <x-imagem :src="$v['channelThumb']" tipo="gde" class="shadow-sm" />
                                        <div class="flex-1">
                                            <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />
                                            <div class="text-gray-500">
                                                Publicado em:
                                                {{ \Carbon\Carbon::parse($v['channelDt'])->format('d/m/Y') }}
                                            </div>
                                            <div class="h-42 text-xs text-justify text-gray-500 mt-1 line-clamp-4">
                                                {{ $v['channelDesc'] ?? '' }}
                                            </div>
                                        </div>
                                    </div>


                                    {{-- KEYWORDS DO CANAL COMO PILLS --}}
                                    @php

                                        $kw = collect(\Illuminate\Support\Arr::wrap($v['channelKeywords'] ?? []))
                                            ->filter(fn($t) => filled($t))
                                            ->values();

                                        $kwShow = $kw->take(8);
                                        $kwMore = max(0, $kw->count() - $kwShow->count());
                                    @endphp

                                    <h4 class="text-lg font-semibold mt-4 mb-1">
                                        Dados do Canal
                                        <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />

                                        — criado em
                                        {{ isset($v['channelDt']) ? \Carbon\Carbon::parse($v['channelDt'])->format('d/m/Y') : '—' }}
                                    </h4>

                                    @if ($kwShow->isNotEmpty())
                                        <x-keywords :items="$kwShow" :more="$kwMore" rows="2" />
                                    @endif

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
                                    </div>

                                    {{-- Rodapé fixado no fundo do card --}}
                                    <div class="mt-auto pt-4">
                                        <x-secondary-button wire:click="escolherMaisPolarizado('{{ $id }}')"
                                            :disabled="$maisPolarizado === $id">
                                            Marcar como mais Polarizado
                                        </x-secondary-button>
                                        @if ($maisPolarizado === $id)
                                            <span class="ml-3 text-indigo-600 text-sm font-semibold">Selecionado</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach

                        </div>
                    </div>

                    <x-primary-button class="w-full text-6xl p-10 mt-6 text-center " wire:click="validarTarefa2"
                        wire:loading.attr="disabled" wire:target="validarTarefa2">
                        Finalizar Avaliação de Polarização

                        <span class="invisible" wire:loading.class.remove="invisible" wire:target="validarTarefa2">
                            <span class="text-sm text-yellow-500">Aguarde Processando ...</span>
                        </span>
                    </x-primary-button>
                </div>
            @endif


            <!-- tabela dos comentarios -->
            @if ($mostrarFeedback)
                <div class="overflow-x-auto mt-8">
                    <table
                        class="divide-y divide-gray-200 divide-solid table-auto min-w-full text-sm tracking-tight leading-tight">
                        <thead>
                            <tr class="bg-gray-100 text-xs text-gray-700 text-center">
                                @php
                                    $videosSessao = session('t2_videos', []);

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
                                                ---</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>


                        <tfoot>
                            <tr
                                class="bg-gray-50 border-t border-gray-300 text-[11px] text-gray-700 font-semibold text-center">
                                @foreach ($polarizMediaArray as $video_id => $polarizMedia)
                                    <td colspan="7"
                                        class="border py-3 text-5xl
                                        {{ $maisPolarizadoReal === $canal_id ? 'bg-indigo-50 text-indigo-900' : 'text-gray-800' }}">
                                        Polariz. média (titulo):
                                        <span class="font-bold text-5xl">
                                            {{ $polarizMedia ? number_format($polarizMedia * 100, 1) . '%' : 'n/a' }}
                                        </span>
                                        @if ($maisPolarizado === $canal_id)
                                            <span
                                                class="ml-2 text-2xl inline-flex items-center rounded-full px-2 py-0.5 
                                            {{ $maisPolarizadoReal === $canal_id ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' }}">
                                                seu palpite
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>

                    <!-- acertou errou e feedback -->
                    <div
                        class="rounded-lg p-4 ring-4 w-full max-w-6xl mx-auto my-4 
                        {{ $acertou ? 'bg-green-50  ring-green-300' : 'bg-red-50 ring-red-300' }}">
                        @if ($acertou)
                            <div class="text-green-800 font-semibold">✅ Você acertou!</div>
                            <div class="text-sm text-green-900">
                                Seu palpite está certo.
                            </div>
                        @else
                            <div class="text-red-800 font-semibold">❌ Você errou.</div>
                            <div class="text-sm text-red-900">
                                Seu palpite está errado.
                            </div>
                        @endif
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

                            <div class=" text-gray-500">

                                Consideramos 10x10 ...
                            </div>
                        </div>
                    </div>




                </div>
            @endif


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




            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const el = document.getElementById('polChart');
                        if (!el || !window.Chart) return;

                        const payload =
                            @json($chart); // {globalStart,min,max,series:{canal:{title,points_title,points_desc,avg,startDay,endDay}}}
                        const globalStart = new Date(payload.globalStart);

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
                        ];

                        const vids = Object.keys(payload.series);
                        const minX = payload.min,
                            maxX = payload.max;

                        // --- monta datasets (círculo = título, "x" = descrição, linha = média) ---
                        const datasets = [];
                        const markers = [];

                        vids.forEach((channelId, idx) => {
                            const s = payload.series[channelId];
                            const color = palette[idx % palette.length];
                            const labelBase = s.title || channelId;

                            // título
                            datasets.push({
                                label: labelBase + ' — título',
                                type: 'scatter',
                                data: s.points_title || [],
                                parsing: false,
                                showLine: false,
                                pointStyle: 'circle',
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                backgroundColor: color.pt,
                                borderColor: color.pt,
                                channelId,
                                metaTipo: 'title'
                            });

                            // descrição
                            datasets.push({
                                label: labelBase + ' — descrição',
                                type: 'scatter',
                                data: s.points_desc || [],
                                parsing: false,
                                showLine: false,
                                pointStyle: 'crossRot',
                                pointRadius: 5,
                                pointHoverRadius: 6,
                                backgroundColor: color.pt,
                                borderColor: color.pt,
                                channelId,
                                metaTipo: 'desc'
                            });

                            // média
                            if (typeof s.avg === 'number') {
                                datasets.push({
                                    label: labelBase + ' — média',
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
                                    channelId,
                                    metaTipo: 'avg'
                                });
                            }

                            // marcadores verticais (início/fim do canal)
                            markers.push({
                                x: s.startDay,
                                color: color.pt
                            });
                            markers.push({
                                x: s.endDay,
                                color: color.pt
                            });
                        });

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

                        // plugin p/ linhas verticais
                        const verticalLines = {
                            id: 'verticalLines',
                            afterDatasetsDraw(chart, args, opts) {
                                const {
                                    ctx,
                                    scales: {
                                        x,
                                        y
                                    }
                                } = chart;
                                (opts.markers || []).forEach(m => {
                                    const xp = x.getPixelForValue(m.x);
                                    ctx.save();
                                    ctx.strokeStyle = m.color;
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

                        // cria gráfico
                        const chart = new Chart(el, {
                            plugins: [verticalLines],
                            data: {
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }, // desliga a legenda padrão
                                    tooltip: {
                                        callbacks: {
                                            title: (items) => {
                                                const day = items[0]?.raw?.x ?? null;
                                                return day != null ? fmt(addDays(globalStart, Number(day))) : '';
                                            },
                                            label: (ctx) => {
                                                const r = ctx.raw || {};
                                                return ` ${ctx.dataset.label}: ${Number(r.y).toFixed(1)}${r.label ? ' — '+r.label : ''}`;
                                            }
                                        }
                                    },
                                    verticalLines: {
                                        markers
                                    }
                                },
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
                                        }
                                    },
                                    y: {
                                        min: -100,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Polarização (-100 .. +100)'
                                        }
                                    }
                                }
                            }
                        });


                        // --- legenda HTML compacta (um pill por canal, com link e toggle) ---
                        const legendHost = document.getElementById('polLegend');
                        const byChannel = {};

                        // use o título vindo do payload.series[channelId].title
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

                        legendHost.innerHTML = '';
                        Object.keys(byChannel).forEach((chId, n) => {
                            const info = byChannel[chId];
                            const color = palette[n % palette.length].pt;

                            // o próprio pill é um <a>
                            const pill = document.createElement('a');
                            pill.href = `https://www.youtube.com/channel/${chId}`;
                            pill.target = '_blank';
                            pill.rel = 'noopener';
                            pill.className = 'px-3 py-1 rounded-full text-sm inline-block select-none';
                            pill.style.border = `1px solid ${color}`;
                            pill.style.color = color;
                            pill.textContent = info.title.length > 40 ? info.title.slice(0, 40) + '…' : info.title;
                            pill.title = info.title;

                            // Comportamento:
                            // - clique normal: toggle visibilidade dos datasets do canal
                            // - Ctrl/Cmd-clique ou clique do meio: deixa abrir o link
                            pill.addEventListener('click', (e) => {
                                if (e.ctrlKey || e.metaKey || e.button === 1) {
                                    // deixa abrir o link em nova aba
                                    return;
                                }
                                e.preventDefault();
                                const anyHidden = info.idxs.some(i => !chart.isDatasetVisible(
                                    i)); // se algum está oculto?
                                info.idxs.forEach(i => chart.setDatasetVisibility(i,
                                    anyHidden)); // toggle todos
                                chart.update();
                            });

                            const wrap = document.createElement('div');
                            wrap.className = 'flex items-center';
                            wrap.appendChild(pill);
                            legendHost.appendChild(wrap);
                        });




                        // --- filtros globais: Título / Descrição / Média ---
                        const cbTitle = document.getElementById('polOnlyTitle');
                        const cbDesc = document.getElementById('polOnlyDesc');
                        const cbAvg = document.getElementById('polOnlyAvg');

                        function applyFilters() {
                            chart.data.datasets.forEach((ds, i) => {
                                const show =
                                    (ds.metaTipo === 'title' && cbTitle.checked) ||
                                    (ds.metaTipo === 'desc' && cbDesc.checked) ||
                                    (ds.metaTipo === 'avg' && cbAvg.checked);
                                chart.setDatasetVisibility(i, show);
                            });
                            chart.update();
                        }
                        [cbTitle, cbDesc, cbAvg].forEach(cb => cb.addEventListener('change', applyFilters));
                        applyFilters(); // aplica estado inicial
                    });
                </script>
            @endpush



        </div>
    </div>
</div>
