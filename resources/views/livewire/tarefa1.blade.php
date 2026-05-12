<div>
    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-rose-600" viewBox="0 0 24 24" fill="none">
                    <path d="M4 5h16a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-7l-4.5 3v-3H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"
                        stroke="currentColor" stroke-width="1.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    Widget 1 — Polarizacao e <span class="text-rose-600">Toxicidade</span>
                </h2>
            </div>
        </div>


{{-- bloco resumido --}}
<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- explicação --}}
    <div class="lg:col-span-2 rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-rose-800 mb-3 uppercase tracking-wide">
            Como utilizar o Widget 1
        </h3>

        <div class="space-y-2 text-sm text-slate-700 leading-6">
            <p>
                Pesquise vídeos sobre um mesmo tema, selecione de <strong>2 a 3 vídeos</strong>
                e clique em <strong>avaliar</strong>.
            </p>

            <p>
                O sistema compara a <strong>polarização discursiva</strong> dos vídeos
                com a <strong>toxicidade dos comentários</strong>, permitindo observar
                se conteúdos mais polarizados também concentram reações mais hostis.
            </p>
        </div>
    </div>

    {{-- legenda --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 mb-3 uppercase tracking-wide">
            Legenda
        </h3>

        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2">
                <span class="text-slate-600">Score P</span>
                <span class="font-semibold text-indigo-700">Polarização</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-sky-100 bg-sky-50 px-3 py-2">
                <span class="text-slate-600">Confiança</span>
                <span class="font-semibold text-sky-700">Classificação IA</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-rose-100 bg-rose-50 px-3 py-2">
                <span class="text-slate-600">Tox. média</span>
                <span class="font-semibold text-rose-700">Comentários</span>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                <span class="text-slate-600">Gráfico</span>
                <span class="font-semibold text-emerald-700">Toxicidade no tempo</span>
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
                    <x-results-table variant="video" :items="$this->buscas" :selected="array_keys($selecionados ?? [])" />
                </div>
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
                                <article wire:key="{{ $id }}"
                                    class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white ring-2 ring-indigo-500">
                                    {{-- Cabeçalho --}}
                                    <div class="flex gap-3">
                                        {{-- <img class="w-40 h-24 object-cover rounded-md"
                                            src="{{ $v['thumbnail'] ?? '' }}" alt="thumb"> --}}
                                        <x-imagem :src="$v['thumbnail']" tipo="gde" class="shadow-sm" />
                                        <div class="flex-1">
                                            <x-linkvideo :videoId="$id" :titulo="$v['videoTitle'] ?? '--'" />
                                            <div class="text-gray-500">
                                                Publicado em:
                                                {{ isset($v['published']) ? \Carbon\Carbon::parse($v['published'])->format('d/m/Y') : '—' }}
                                            </div>
                                            <div class="h-42 text-xs text-justify text-gray-500 mt-1 line-clamp-4">
                                                {{ $v['videoDesc'] ?? 'sem descrição' }}
                                            </div>
                                        </div>
                                    </div>

                                    <x-keywords :items="$v['videoTags'] ?? []" limit="8" rows="2" />

                                    <h4 class="text-lg font-semibold mt-4 mb-0">Dados do Vídeo</h4>
                                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                        <div class="bg-gray-50 p-2 rounded">
                                            <div class="text-gray-500">Duração</div>
                                            <div class="font-semibold">{{ $v['duration'] ?? '-' }} s</div>
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded">
                                            <div class="text-gray-500">Views</div>
                                            <div class="font-semibold">
                                                {{ number_format($v['viewCount'] ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded">
                                            <div class="text-gray-500">Likes</div>
                                            <div class="font-semibold">
                                                {{ number_format($v['likeCount'] ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded">
                                            <div class="text-gray-500">Comentários</div>
                                            <div class="font-semibold">
                                                {{ number_format($v['commentCount'] ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                    </div>


                                    <h4 class="text-lg font-semibold mt-4 mb-1">
                                        Dados do Canal
                                        <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? '—'" />

                                        criado em
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
                                    </div>

                                </article>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="overflow-x-auto mt-8">
                    <table
                        class="divide-y divide-gray-200 divide-solid table-auto min-w-full text-sm tracking-tight leading-tight">
                        <thead>
                            <tr class="bg-gray-100 text-xs text-gray-700 text-center">
                                @php
                                    $comentariosSessao = $samples;

                                    $numComents = max(count($comentariosSessao), 1); // evita /0
                                    $colWidth = number_format(100 / ($numComents * 5), 2);
                                @endphp
                                @foreach ($comentariosSessao as $video_id => $dados)
                                    <th colspan="5" style="width: {{ $colWidth * 5 }}%;"
                                        class="border border-gray-300 px-2 py-4">
                                        <x-linkvideo :videoId="$video_id" :titulo="$selecionados[$video_id]['videoTitle'] ?? '--'" />
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $max = collect($comentariosSessao)->map(fn($d) => count($d))->max() ?? 0;
                            @endphp

                            @for ($i = 0; $i < $max; $i++)
                                @if ($i == 0)
                                    <tr
                                        class="border border-gray-300 w-[10px] font-bold text-center text-indigo-800 text-[10px] ">
                                        <td>#</td>
                                        <td>Comentário</td>
                                        <td>Likes</td>
                                        <td>Data</td>
                                        <td>Tox</td>
                                        <td>#</td>
                                        <td>Comentário</td>
                                        <td>Likes</td>
                                        <td>Data</td>
                                        <td>Tox</td>
                                    </tr>
                                @endif

                                <tr class="">
                                    @foreach ($comentariosSessao as $loopIndex => $dados)
                                        @php
                                            $c = $dados[$i] ?? null;

                                            #dump($c);

                                        @endphp

                                        @if ($c)
                                            <td
                                                class="border border-gray-300 w-[10px] text-left text-gray-800 text-[10px] ">
                                                {{ $i + 1 }}</td>

                                            <td
                                                class="border border-gray-300 w-[420px] max-w-[420px] truncate break-all">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($c['texto'] ?? '[sem texto]'), 120) }}
                                            </td>

                                            <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                {{ $c['likes'] ?? 0 }}</td>
                                            <td class="border border-gray-300 w-[20px] text-gray-800 text-[10px]">
                                                {{ isset($c['dt']) ? \Carbon\Carbon::parse($c['dt'])->format('d/m/Y') : '--' }}
                                            </td>
                                            <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                {{ isset($c['tox']) ? number_format($c['tox'] * 100, 1) . '%' : 'X' }}
                                            </td>
                                        @else
                                            <td colspan="5"
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
                                @foreach ($toxMediaArray as $video_id => $toxMedia)
                                    <td colspan="5"
                                        class="border py-3 text-5xl
                                        {{ $maisToxicoReal === $video_id ? 'bg-indigo-50 text-indigo-900' : 'text-gray-800' }}">
                                        Tox. média:
                                        <span class="font-bold text-5xl">
                                            {{ $toxMedia ? number_format($toxMedia * 100, 1) . '%' : 'n/a' }}
                                        </span>

                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>

                    <!-- acertou errou e feedback -->
                    @if (empty($this->feedback))
                        <div class="rounded-lg p-4 ring-4 w-full max-w-6xl mx-auto my-4 bg-green-50 ring-green-300">
                            <div class="mt-4 grid gap-2">
                                <label class="text-sm font-medium text-gray-700">
                                    Deixe um breve feedback: por que você escolheu esse vídeo?
                                </label>
                                <textarea rows="3" wire:model.defer="feedback"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: Pela thumbnail e título, achei que geraria mais comentários agressivos..."></textarea>

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
                                    Como comparamos: calculamos a toxicidade média dos comentários raiz de
                                    cada vídeo via Perspective API.<br>
                                    Em caso de empate técnico (diferenças muito pequenas), o resultado é
                                    "inconclusivo".<br>
                                    Vale ressaltar que consideramos somente videos com menos de 100 comentarios raiz na
                                    busca
                                    por questoes financeiras.
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

            @endif

        </div>
    </div>

    @php
        $t1 = session('t1_result', []);
        $selecionados = $t1['selecionados'] ?? [];
        $polarizacoes = $this->polarizacoes ?? [];
        $toxMedias = $t1['tox_media'] ?? [];
    @endphp


    @if (!empty($polarizacoes) && count($selecionados) > 0)
        <div class="mx-auto p-6 w-full max-w-[1400px]">
            <h2 class="text-xl font-semibold mb-4">Classificação de polarização dos vídeos selecionados</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach ($polarizacoes as $videoId => $pol)
                    @php
                        $raw = $selecionados[$videoId] ?? [];
                        $tox = $toxMedias[$videoId] ?? null;

                        // DEFINE COR PELO ÍNDICE (igual chart)
                        $idx = array_search($videoId, array_keys($polarizacoes));

                        if ($idx === 0) {
                            $border = 'border-green-500';
                            $bg = 'bg-green-50';
                            $accent = 'text-green-700';
                            $tag = 'bg-green-100 text-green-700';
                        } else {
                            $border = 'border-red-500';
                            $bg = 'bg-red-50';
                            $accent = 'text-red-700';
                            $tag = 'bg-red-100 text-red-700';
                        }
                    @endphp

                    <div
                        class="rounded-xl border-2 {{ $border }} {{ $bg }} p-4 shadow-md hover:shadow-lg transition">

                        <div class="flex justify-between items-center mb-2">
                            <div class="text-xs text-slate-500">
                                ID: {{ $videoId }}
                            </div>

                            <span class="px-2 py-1 text-xs rounded {{ $tag }}">
                                Vídeo {{ $idx + 1 }}
                            </span>
                        </div>

                        <h3 class="font-semibold {{ $accent }} leading-snug mb-3">
                            {{ $raw['videoTitle'] ?? $videoId }}
                        </h3>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-slate-500">Categoria</span><br>
                                <strong>{{ $pol['categoria'] ?? 'n/d' }}</strong>
                            </div>

                            <div>
                                <span class="text-slate-500">Polo</span><br>
                                <strong>{{ $pol['polo_ideologico'] ?? 'indefinido' }}</strong>
                            </div>

                            <div>
                                <span class="text-slate-500">Score P</span><br>
                                <strong>
                                    {{ isset($pol['polarizacao_score']) ? number_format($pol['polarizacao_score'], 2, ',', '.') : '-' }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-slate-500">Confiança</span><br>
                                <strong>
                                    {{ isset($pol['confianca']) ? number_format($pol['confianca'], 2, ',', '.') : '-' }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-slate-500">Tox. média</span><br>
                                <strong class="{{ $accent }}">
                                    {{ $tox !== null ? number_format($tox * 100, 2, ',', '.') . '%' : '-' }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-slate-500">Transcript</span><br>
                                <strong>{{ $pol['transcript_words'] ?? 0 }} palavras</strong>
                            </div>
                        </div>

                        @if (!empty($pol['justificativa']))
                            <p class="mt-3 text-xs text-slate-600 border-t pt-2">
                                {{ $pol['justificativa'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif


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
            window.addEventListener('t1-chart-updated', (event) => {
                const chartData = event.detail?.chart ?? null;
                console.log('t1-chart-updated recebido:', chartData);

                if (chartData && chartData.series && Object.keys(chartData.series).length) {
                    renderChart(chartData);
                } else {
                    console.warn('t1-chart-updated veio sem séries', chartData);
                }
            });


        })();
    </script>
@endpush
