<div>

    <x-slot name="header">
        <div x-data="{
            open: JSON.parse(localStorage.getItem('tarefa4_header_open') ?? 'true')
        }" x-init="$watch('open', v => localStorage.setItem('tarefa4_header_open', JSON.stringify(v)))" class="relative">
            <!-- Barra do título + botão -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Tarefa4 - Intersecçao') }}
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
            <div id="t4-instrucoes" x-show="open" x-transition.opacity.scale.origin.top x-cloak
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
                            Tarefa 4 — Evolução de <span class="text-emerald-700">engajamento</span> e <span
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
                    clear="clearSelecionados" evaluate="avaliarCanais" :min="2" />

                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />

                <x-results-table-4 :items="$buscas" :checked="$checked" :selected="array_keys($selecionados ?? [])" />
            </div>


            <div class="grid grid-cols-1">

                <div id="avaliacao" class="mt-8 px-4">
                    <x-primary-button class="w-full text-6xl p-10 mt-6 text-center " wire:click="addTodos"
                        wire:loading.attr="disabled" wire:target="addTodos">
                        Adicionar Todos para Avaliação
                        <span class="invisible" wire:loading.class.remove="invisible" wire:target="addTodos">
                            <span class="text-sm text-yellow-500">Aguarde Processando ...</span>
                        </span>
                    </x-primary-button>
                </div>

                <div id="monetiz" class="mt-8 px-4">
                    <x-primary-button class="w-full text-6xl p-10 mt-6 text-center "
                        wire:click="repopularMonetizSelecionados" wire:loading.attr="disabled"
                        wire:target="repopularMonetizSelecionados">
                        Repopular Monetização Selecionados
                        <span class="invisible" wire:loading.class.remove="invisible"
                            wire:target="repopularMonetizSelecionados">
                            <span class="text-sm text-yellow-500">Aguarde Processando ...</span>
                        </span>
                    </x-primary-button>
                </div>

                <div id="processamento" class="mt-8 px-4">
                    <x-primary-button class="w-full text-6xl p-10 mt-6 text-center " wire:click="validarTarefa4"
                        wire:loading.attr="disabled" wire:target="validarTarefa4">
                        Popular Polarização e Salvar BD
                        <span class="invisible" wire:loading.class.remove="invisible" wire:target="validarTarefa4">
                            <span class="text-sm text-yellow-500">Aguarde Processando ...</span>
                        </span>
                    </x-primary-button>
                </div>


            </div>


        </div>

    </div>






    <div class="mt-6 flex justify-center">
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
                                        return `${p.label} | Pol: ${pol}% | Monet: ${monet}`;
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
                                    text: 'Monetização (escala relativa)'
                                },
                                ticks: {
                                    callback: v => fmtNum.format(v)
                                }
                            },
                            y: {
                                min: -100, // polarização sempre -100 a +100
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Polarização (%)'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush




</div>
