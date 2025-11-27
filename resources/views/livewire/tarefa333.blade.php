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

                                        <x-cardcanal :v="$v" />


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
                                                    <span class="text-lg font-semibold text-green-700 md:text-xl">U$
                                                        {{ isset($v['monetAvgUsd']) ? $v['monetAvgUsd'] . '.00 /mes' : '' }}</span>
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
                                                        class="text-lg font-semibold md:text-xl">{{ $v['minutagemTotalFmt'] ?? '' }}</span>
                                                    -
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
                                wire:target="validarTarefa3">
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
                                        $videosSessao = session('t3_videos', []);

                                    @endphp
                                </tr>
                            </thead>



                            <tfoot>
                                <tr
                                    class="bg-gray-50 border-t border-gray-300 text-[11px] text-gray-700 font-semibold text-center">
                                    definiir os U$
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


            </div>
        </div>
    </div>
</div>



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fmtNum = new Intl.NumberFormat('pt-BR');

            const niceMaxY = raw => {
                const pow = Math.pow(10, Math.floor(Math.log10(raw || 1)));
                for (const k of [1, 2, 2.5, 5, 10]) {
                    const c = k * pow;
                    if (c >= raw) return c;
                }
                return 10 * pow;
            };

            function makeChart() {
                const canvas = document.getElementById('chartPolarMonet');
                if (!canvas) return;
                if (canvas.dataset.chartInit === '1') return;
                canvas.dataset.chartInit = '1';

                const rawPoints = canvas.dataset.points || '[]';
                const yMin = parseFloat(canvas.dataset.yMin ?? 0);
                const yMax = parseFloat(canvas.dataset.yMax ?? 1);

                let points = [];
                try {
                    points = JSON.parse(rawPoints);
                } catch (e) {
                    console.error('Erro parse points:', e);
                }

                if (!points.length) return;

                const ctx = canvas.getContext('2d');
                const yMaxNice = niceMaxY(yMax * 1.1);

                // destrói gráfico anterior se existir
                if (window.t4ChartInstance) window.t4ChartInstance.destroy();

                window.t4ChartInstance = new Chart(ctx, {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Canais',
                            data: points,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        const p = ctx.raw;
                                        return `${p.label} | Pol: ${p.x.toFixed(2)}% | Monet: ${fmtNum.format(p.y)}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                min: -100,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Polarização (%)'
                                }
                            },
                            y: {
                                min: yMin,
                                max: yMaxNice,
                                title: {
                                    display: true,
                                    text: 'Monetização (escala relativa)'
                                }
                            }
                        }
                    }
                });
            }

            function initCharts() {
                makeChart();
            }

            initCharts();

            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('message.processed', () => {
                    if (window.t4ChartInstance) window.t4ChartInstance.destroy();
                    document.getElementById('chartPolarMonet').dataset.chartInit = '0';
                    initCharts();
                });
            });
        });
    </script>
@endpush
