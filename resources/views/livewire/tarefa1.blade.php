<div>






    <x-slot name="header">
        <div x-data="{
            open: JSON.parse(localStorage.getItem('tarefa2_header_open') ?? 'true')
        }" x-init="$watch('open', v => localStorage.setItem('tarefa2_header_open', JSON.stringify(v)))" class="relative">
            <!-- Barra do título + botão -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Tarefa1 - Toxicidade de audiencia') }}
                </h2>

                <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                    :aria-expanded="open" aria-controls="t1-instrucoes">
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
            <div id="t1-instrucoes" x-show="open" x-transition.opacity.scale.origin.top x-cloak
                class="bg-white shadow-sm rounded-2xl p-6 md:p-8 border">

                <!-- INICIO -->


                <!-- título + subtítulo -->
                <div class="flex items-start gap-4">
                    <!-- ícone “analisar vídeo” -->
                    <svg class="w-12 h-12 shrink-0 text-indigo-600" viewBox="0 0 24 24" fill="none"
                        aria-hidden="true">
                        <rect x="2" y="4" width="20" height="14" rx="3" stroke="currentColor"
                            stroke-width="1.5" />
                        <path d="M10 11.5v-3l4 2-4 2z" fill="currentColor" />
                        <circle cx="7" cy="16.5" r="1" fill="currentColor" />
                        <circle cx="11" cy="16.5" r="1" fill="currentColor" />
                        <circle cx="15" cy="16.5" r="1" fill="currentColor" />
                    </svg>

                    <div>
                        <h2 class="text-2xl md:text-3xl font-semibold leading-tight">
                            Tarefa 1 — Qual vídeo gera <span class="text-rose-600">reações mais tóxicas</span>?
                        </h2>
                        <p class="mt-1 text-slate-600">
                            Você verá os <strong>metadados</strong> de 2–3 vídeos (título, descrição, tags, miniatura,
                            idioma, etc.).
                            <span class="font-medium">Sem ler comentários</span>, escolha aquele que você acredita gerar
                            <strong>comentários raiz mais tóxicos</strong>.
                        </p>
                    </div>
                </div>

                <!-- faixa ilustrativa -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <!-- ícone “comentários tóxicos” -->
                        <svg class="w-10 h-10 text-rose-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M4 5h16a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-7l-4.5 3v-3H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"
                                stroke="currentColor" stroke-width="1.5" />
                            <circle cx="9" cy="10" r="1" fill="currentColor" />
                            <circle cx="15" cy="10" r="1" fill="currentColor" />
                            <path d="M8 13.5c1.2-.8 2.8-.8 4 0" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Hipótese do participante</h3>
                            <p class="text-slate-600 text-sm">
                                Com base apenas no que o vídeo “comunica” (título, descrição, visual, palavras-chave),
                                qual deles tende a provocar reações mais ásperas no público?
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <!-- ícone “método/avaliação” -->
                        <svg class="w-10 h-10 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                            <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M8 13c1.3-1 3.7-1 5 0" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Como validaremos sua escolha</h3>
                            <p class="text-slate-600 text-sm">
                                Nós coletamos uma amostra de <strong>comentários raiz</strong> por <em>relevância</em>
                                (otimizando o número de
                                chamadas de API) e calculamos a <strong>média de toxicidade</strong> usando a
                                <a class="text-indigo-600 underline" href="https://www.perspectiveapi.com/"
                                    target="_blank" rel="noopener">Perspective API</a>
                                (Google/Jigsaw), amplamente usada em pesquisas acadêmicas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- instruções -->
                <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que você faz</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Pesquise um tema ou cole um vídeo específico.</li>
                            <li>Selecione <strong>2 a 3 vídeos</strong> para comparar.</li>
                            <li>Com base nos metadados, <strong>marque o mais tóxico</strong>.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que nós calculamos</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Coleta de comentários raiz por <em>relevância</em> (≥ 100 quando possível).</li>
                            <li>Atribuição de <em>score</em> de toxicidade por comentário via Perspective API.</li>
                            <li>Média geral por vídeo e gráfico com a distribuição.</li>
                        </ul>
                    </div>
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">Quando termina</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Mostramos o <strong>resultado</strong> (acertou/errou) e o gráfico das médias.</li>
                            <li>Você deixa um <strong>feedback</strong> rápido sobre sua decisão.</li>
                        </ul>
                    </div>
                </div>

                <!-- dica rápida -->
                <div class="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-900 text-sm">
                    <span class="font-semibold">Dica:</span> foque em sinais de “confronto” nos metadados —
                    <em>palavras-chave polarizadoras</em>, tom do título/miniatura e framing da descrição.
                </div>



                <!-- FIM -->

            </div>

        </div>
    </x-slot>







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
                                @php
                                    #dump($id);
                                    #dd($v);
                                @endphp
                                <article wire:key="{{ $id }}"
                                    class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white
                                     {{ $maisToxico === $id ? 'ring-2 ring-indigo-500' : '' }}">

                                    {{-- Cabeçalho --}}
                                    <div class="flex gap-3">
                                        <img class="w-40 h-24 object-cover rounded-md"
                                            src="{{ $v['thumbnail'] ?? '' }}" alt="thumb">
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

                                    {{-- Rodapé fixado no fundo do card --}}
                                    <div class="mt-auto pt-4">
                                        <x-secondary-button wire:click="escolherMaisToxico('{{ $id }}')"
                                            :disabled="$maisToxico === $id">
                                            Marcar como mais tóxico
                                        </x-secondary-button>
                                        @if ($maisToxico === $id)
                                            <span class="ml-3 text-indigo-600 text-sm font-semibold">Selecionado</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach

                        </div>
                    </div>

                    <x-primary-button class="w-full text-6xl p-10 mt-6 text-center " wire:click="validarTarefa1"
                        wire:loading.attr="disabled" wire:target="validarTarefa1">
                        Finalizar Avaliação de Toxicidade

                        <span class="invisible" wire:loading.class.remove="invisible" wire:target="validarTarefa1">
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
                                    #$comentariosSessao = session('t1_comentarios', []);
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
                                        @if ($maisToxico === $video_id)
                                            <span
                                                class="ml-2 text-2xl inline-flex items-center rounded-full px-2 py-0.5 
                                            {{ $maisToxicoReal === $video_id ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' }}">
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

                </div>






            @endif

        </div>
    </div>



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
            if (!window._toxCharts) window._toxCharts = {};

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


        })();
    </script>
@endpush
