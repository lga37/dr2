<div>


    {{-- WIDGET 4 | Cabeçalho informativo --}}
    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">

        {{-- título --}}
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-emerald-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 18V7m5 11V5m5 13V9m5 9V4" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" />
                </svg>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    WIDGET 4 — Intersecção entre <span class="text-emerald-700">polarizacao</span>, <span class="text-emerald-700">toxicidade</span> e <span
                        class="text-emerald-700">monetização</span>
                </h2>
                <p class="mt-1 text-slate-600 text-sm md:text-base max-w-7xl">
                    Compare <strong>2 canais</strong> e observe como <strong>toxicidade</strong> e <strong>rentabilidade
                        estimada</strong>
                    evoluem no tempo, investigando possíveis relações entre dinâmica de engajamento e incentivos
                    econômicos.
                </p>
            </div>
        </div>

        {{-- boxes --}}
        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

            {{-- O que você faz --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">O que você faz</h3>
                <p class="text-slate-700">
                    Selecione canais por <strong>tema (busca)</strong> ou via <strong>ID/URL</strong>.
                    Use <strong>Adicionar checados</strong> e depois clique em <strong>Avaliar canais</strong>.
                </p>
            </div>

            {{-- O que você verá --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">O que você verá</h3>
                <p class="text-slate-700">
                    Um gráfico temporal onde o <strong>eixo X</strong> é o <strong>tempo</strong> e o <strong>eixo
                        Y</strong>
                    é a <strong>monetização estimada</strong> (<em>USD/mês</em>), além de indicadores que permitem
                    comparar os canais ao longo do período.
                </p>
            </div>

            {{-- O que analisamos --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">O que analisamos</h3>
                <p class="text-slate-700">
                    A relação entre <strong>toxicidade</strong> e <strong>monetização</strong> no tempo,
                    buscando padrões de co-variação, mudanças de tendência e diferenças entre canais.
                </p>
            </div>

        </div>

        {{-- nota metodológica / premissas --}}
        <div class="mt-5 p-4 rounded-xl bg-sky-50 border border-sky-100 text-sky-900 text-sm">
            <span class="font-semibold">Nota metodológica:</span>
            os valores de monetização são <strong>estimativas</strong> derivadas de fontes públicas e premissas que
            serão detalhadas na tese.
            O objetivo aqui é oferecer uma visualização exploratória, de caráter <strong>acadêmico</strong>, para
            investigar a intersecção entre
            incentivos econômicos e dinâmica de toxicidade.
        </div>

        {{-- aviso de performance --}}
        <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-900 text-sm">
            <span class="font-semibold">Atenção:</span>
            esta etapa pode ser <strong>mais lenta</strong>, pois o processamento envolve múltiplas coletas e
            consolidações de séries no tempo.
        </div>

        {{-- feedback --}}
        <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-900 text-sm">
            <span class="font-semibold">Ao final:</span>
            pedimos um <strong>feedback</strong> sobre a utilidade do WIDGET, possíveis usos (para pesquisadores e
            público geral)
            e, se possível, <strong>sugestões de melhoria</strong>.
        </div>

    </div>


    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <x-selecionados-table :items="$selecionados" type="canal" remove="removeSelecionado"
                    clear="clearSelecionados" evaluate="validarTarefa4" :min="2" />

                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />

                <x-results-table-4 :items="$buscas" :checked="$checked" :selected="array_keys($selecionados ?? [])" />
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

                    {{-- PASSO 2 --}}
                    {{-- <div id="monetiz" class="flex-1">
                        <x-primary-button wire:click="repopularMonetizSelecionados" wire:loading.attr="disabled"
                            wire:target="repopularMonetizSelecionados"
                            class="w-full flex items-center justify-start gap-2 px-3 py-4 rounded-2xl text-left
                       bg-emerald-200 hover:bg-emerald-300 border border-emerald-300">

                            <span
                                class="flex items-center justify-center h-16 w-16 rounded-full
                             bg-emerald-600 text-white text-4xl font-extrabold leading-none">
                                2
                            </span>

                            <div class="flex flex-col">
                                <span class="text-lg font-semibold">
                                    (Re)Popular Monetização
                                </span>
                                <span class="text-xs text-emerald-800">
                                    Segundo passo (dados de monetização)
                                </span>
                            </div>

                            <span class="ml-auto invisible" wire:loading.class.remove="invisible"
                                wire:target="repopularMonetizSelecionados">
                                <span class="text-sm text-yellow-500">
                                    Aguarde processando...
                                </span>
                            </span>
                        </x-primary-button>
                    </div> --}}

                    {{-- PASSO 3 --}}
                    {{-- <div id="processamento" class="flex-1">
                        <x-primary-button wire:click="validarTarefa4" wire:loading.attr="disabled"
                            wire:target="validarTarefa4"
                            class="w-full flex items-center justify-start gap-2 px-3 py-4 rounded-2xl text-left
                       bg-emerald-300 hover:bg-emerald-400 border border-emerald-400">

                            <span
                                class="flex items-center justify-center h-16 w-16 rounded-full
                             bg-emerald-700 text-white text-4xl font-extrabold leading-none">
                                2
                            </span>

                            <div class="flex flex-col">
                                <span class="text-lg font-semibold">
                                    Popular Toxicidade
                                </span>
                                <span class="text-xs text-emerald-900">
                                    Ultimo passo (finalização)
                                </span>
                            </div>

                            <span class="ml-auto invisible" wire:loading.class.remove="invisible"
                                wire:target="validarTarefa4">
                                <span class="text-sm text-yellow-500">
                                    Aguarde processando...
                                </span>
                            </span>
                        </x-primary-button>
                    </div> --}}

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

                    <div class=" text-gray-500">
                        Metodologia aplicada:
                    </div>
                </div>
            @else
                Percorra os passos da tarefa
            @endif


        </div>

    </div>

    <div class="mt-0 flex justify-center">
        <div id="hello-wrapper" style="width: 800px; height: 300px; max-width: 100%; border: 1px solid #eee;">
            <canvas id="chartHello"></canvas>
        </div>
    </div>

    @php
        $points = [];

        # dump($selecionados);

        foreach ($selecionados as $row) {
            // só plota quem já tem monetização e polarização
            if (!isset($row['monetiz'], $row['polariz'])) {
                continue;
            }

            $points[] = [
                'x' => (float) $row['monetiz'], // eixo X = monetização
                'y' => (float) $row['polariz'], // eixo Y = polarização
                'label' => (string) ($row['channelTitle'] ?? ($row['nome'] ?? ($row['handle'] ?? 'Canal'))),
            ];
        }

        # dd($points);

        // faixa do eixo X baseada na monetização
        if (count($points) > 0) {
            $xs = array_column($points, 'x');
            $minX = min($xs);
            $maxX = max($xs);

            $range = $maxX - $minX;
            $padding = $range * 0.1;

            // aplica padding pra cima sempre
            $maxX += $padding;

            // pra baixo, nunca deixa passar de 0
            $minX = max(0, $minX - $padding);

            // se todo mundo tiver o mesmo valor, evita min == max
            if ($minX === $maxX) {
                $minX = max(0, $minX - 1);
                $maxX += 1;
            }
        } else {
            $minX = 0;
            $maxX = 1;
        }

    @endphp


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const wrapper = document.getElementById('hello-wrapper');
                const canvas = document.getElementById('chartHello');

                if (!wrapper || !canvas || typeof Chart === 'undefined') return;

                const ctx = canvas.getContext('2d');

                // deixa o canvas exatamente do tamanho do wrapper
                canvas.width = wrapper.clientWidth;
                canvas.height = wrapper.clientHeight;

                const dataPoints = @json($points);
                const xMin = {{ $minX }};
                const xMax = {{ $maxX }};

                if (!dataPoints.length) {
                    return;
                }

                const fmtNum = new Intl.NumberFormat('pt-BR');

                new Chart(ctx, {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Canais',
                            data: dataPoints,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb',
                        }]
                    },
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        const p = context.raw;
                                        const monet = fmtNum.format(p.x); // X = monet
                                        const pol = p.y.toFixed(2); // Y = polar
                                        return `${p.label} | Tox: ${pol}% | Monet: ${monet}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                min: xMin, // baseado na monetização
                                max: xMax,
                                title: {
                                    display: true,
                                    text: 'Monetização (U$ / mes)'
                                },
                                ticks: {
                                    callback: v => fmtNum.format(v)
                                }
                            },
                            y: {
                                min: 0, // polarização sempre -100 a +100
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Toxicidade (%)'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush




</div>
