<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    {{-- HERO / INTRO --}}
    <section class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Monetização no YouTube</h1>
            <p class="mt-4 text-zinc-700">
                Priorizamos a <span class="font-medium">monetização nativa</span> do YouTube (YPP: anúncios, Membros,
                Super Chat/Super Thanks),
                pois ela tende a ser o principal determinante de receita e incentivos. Mesmo sob a “caixa-preta” do
                algoritmo,
                conseguimos medir proxies e sinais por <span class="font-medium">fontes públicas</span> (APIs, páginas de
                canal/vídeo, webarchive e
                scraping leve), permitindo estimativas robustas em nível de segmento.
            </p>
            <ul class="mt-4 space-y-2 text-zinc-700">
                <li>• <span class="font-medium">Nativo (YouTube):</span> Ads suitability, Membros, Super Chat/Thanks,
                    marcações de conteúdo, frequência de uploads.</li>
                <li>• <span class="font-medium">Alternativo (externo):</span> links e frases de pedido de apoio na
                    descrição (Pix, PicPay, PayPal, Bitcoin,
                    BuyMeACoffee, Patreon, Apoia.se etc.).</li>
                <li>• <span class="font-medium">Estratégia:</span> medir o nativo e mapear o alternativo via varredura
                    automática das descrições.</li>
            </ul>
        </div>

        {{-- STATS BOX (placeholders fáceis de trocar) --}}
        <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
            <h3 class="font-medium text-zinc-800">YouTube em números (caixa ilustrativa)</h3>
            <p class="text-xs text-zinc-500 mb-4">Valores meramente exemplificativos — troque quando quiser.</p>
            <dl class="grid sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Usuários ativos/mês</dt>
                    <dd class="text-lg font-semibold text-zinc-900">≈ 2.5B</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Horas assistidas/dia</dt>
                    <dd class="text-lg font-semibold text-zinc-900">≈ 1B+</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Uploads por minuto</dt>
                    <dd class="text-lg font-semibold text-zinc-900">≈ 500+</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Canais no YPP</dt>
                    <dd class="text-lg font-semibold text-zinc-900">≈ —</dd>
                </div>
            </dl>
            <div class="mt-4 text-xs text-zinc-500">
                Observação: o número de <em>canais</em> no recorte de um segmento é sempre ≤ ao número de
                <em>vídeos</em> retornados pela query
                (um canal pode aparecer com múltiplos vídeos).
            </div>
        </div>
    </section>

    {{-- BLOCO: SEGMENTO + GRÁFICOS --}}
    <section class="mt-14">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold">Monetização alternativa por segmento</h2>
            <div class="text-sm text-zinc-500">Exemplo com dados fictícios do segmento: <span
                    class="font-medium">“aborto”</span></div>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-3">
            {{-- BARRAS: plataformas/formas de apoio detectadas na descrição --}}
            <div class="lg:col-span-2 bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Plataformas / formas de apoio detectadas</h3>
                    <span class="text-xs text-zinc-500">Fonte: parsing de descrições (crawler)</span>
                </div>
                <div class="relative" style="height:340px">
                    <canvas id="altMonetBar"></canvas>
                </div>
                <p class="mt-3 text-xs text-zinc-500">
                    Contagem de ocorrências por plataforma (domínios e palavras-chave na descrição dos <em>canais</em>
                    e/ou
                    <em>vídeos</em> do segmento).
                </p>
            </div>

            {{-- DOUGHNUT: TLDs dos links externos --}}
            <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Distribuição de TLDs (links externos)</h3>
                    <span class="text-xs text-zinc-500">Exemplo ilustrativo</span>
                </div>
                <div class="relative" style="height:340px">
                    <canvas id="tldDoughnut"></canvas>
                </div>
            </div>
        </div>
    </section>


    {{-- CARDS: CPM x RPM + YPP --}}
    <section class="mt-14 grid gap-6 lg:grid-cols-2">

        {{-- CARD 1 — CPM x RPM --}}
        <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
            <header class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">CPM x RPM — o que medem e como calcular</h3>
                    <p class="text-sm text-zinc-600">Entenda a diferença entre o que o <em>anunciante paga</em> e o que
                        você <em>realmente recebe</em>.</p>
                </div>
                <!-- ícone simples -->
                <svg class="w-8 h-8 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" d="M3 3h18v4H3zM3 10h18v11H3z" />
                    <path stroke-width="1.5" d="M7 14h3v5H7zM12 12h3v7h-3zM17 16h3v3h-3z" />
                </svg>
            </header>

            <div class="mt-4 grid sm:grid-cols-2 gap-4">
                <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-100 p-4">
                    <h4 class="font-medium text-emerald-900">CPM (Cost per Mille)</h4>
                    <p class="mt-2 text-sm text-emerald-900/80">
                        Quanto o <span class="font-medium">anunciante paga</span> por 1.000 impressões de anúncio. É um
                        <span class="font-medium">indicador do mercado</span>,
                        não do seu ganho final.
                    </p>
                    <p class="mt-2 text-xs text-emerald-900/70">
                        Fórmula (anunciante): <code>CPM = (Gasto em anúncios / Impressões) × 1000</code>.
                        
                    </p>
                </div>

                <div class="rounded-xl bg-indigo-50 ring-1 ring-indigo-100 p-4">
                    <h4 class="font-medium text-indigo-900">RPM (Revenue per Mille)</h4>
                    <p class="mt-2 text-sm text-indigo-900/80">
                        Quanto você <span class="font-medium">ganhou por 1.000 visualizações</span> do vídeo (soma ads +
                        Premium + Membros + Super Chat/Thanks etc.).
                        É o <span class="font-medium">indicador do criador</span>.
                    </p>
                    <p class="mt-2 text-xs text-indigo-900/70">
                        Fórmula (YouTube/AdSense): <code>RPM = (Receita estimada / Visualizações) × 1000</code>.
                        No Shorts, o RPM usa <em>engaged views</em>. 
                    </p>
                </div>
            </div>

            <ul class="mt-4 text-sm text-zinc-700 space-y-2">
                <li>• CPM alto não garante RPM alto (há intermediações, formatos, localização e elegibilidade de
                    anúncios).</li>
                <li>• RPM agrega múltiplas fontes de receita (ads, Premium, membros, fan funding).
                    </li>
            </ul>
        </article>

        {{-- CARD 2 — YPP: como entrar (nível inicial e completo) --}}
        <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
            <header class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">YouTube Partner Program (YPP) — elegibilidade</h3>
                    <p class="text-sm text-zinc-600">Requisitos válidos hoje (nível inicial e monetização completa).</p>
                </div>
                <svg class="w-8 h-8 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" d="M4 7h16M4 12h16M4 17h10" />
                    <circle cx="18" cy="17" r="3" stroke-width="1.5" />
                </svg>
            </header>

            <div class="mt-4 grid sm:grid-cols-2 gap-4">
                <div class="rounded-xl bg-zinc-50 ring-1 ring-zinc-200 p-4">
                    <h4 class="font-medium">Nível inicial (fan funding / loja)</h4>
                    <ul class="mt-2 text-sm text-zinc-700 space-y-1.5">
                        <li>• <span class="font-medium">≥ 500</span> inscritos</li>
                        <li>• <span class="font-medium">3 uploads públicos</span> nos últimos 90 dias</li>
                        <li>• E <span class="font-medium">3.000 h</span> de exibição nos últimos 12 meses <em>ou</em>
                            <span class="font-medium">3M</span> views de Shorts em 90 dias</li>
                    </ul>
                    <p class="mt-2 text-xs text-zinc-500">Elegível a recursos como Membros, Super Chat/Thanks e
                        Shopping. </p>
                </div>

                <div class="rounded-xl bg-zinc-50 ring-1 ring-zinc-200 p-4">
                    <h4 class="font-medium">Monetização completa de anúncios</h4>
                    <ul class="mt-2 text-sm text-zinc-700 space-y-1.5">
                        <li>• <span class="font-medium">≥ 1.000</span> inscritos</li>
                        <li>• <span class="font-medium">4.000 h</span> de exibição em 12 meses <em>ou</em> <span
                                class="font-medium">10M</span> views de Shorts em 90 dias</li>
                    </ul>
                    <p class="mt-2 text-xs text-zinc-500">Requisito clássico do YPP para participação em receita de
                        anúncios. </p>
                </div>
            </div>

            <p class="mt-4 text-xs text-amber-700 bg-amber-50 rounded-lg p-3 ring-1 ring-amber-100">
                Nota: as políticas do YPP são atualizadas periodicamente (ex.: diretrizes sobre conteúdo
                repetitivo/inautêntico). Vale conferir as páginas oficiais
                antes de apresentar números em banca. 
            </p>
        </article>

    </section>



    {{-- METODOLOGIA RESUMO --}}
    <section class="mt-14 grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl ring-1 ring-zinc-200 p-6">
            <h3 class="font-medium">Como mapeamos a monetização alternativa</h3>
            <ol class="mt-3 space-y-2 text-zinc-700">
                <li>1) Definir o <span class="font-medium">segmento</span> (query/POI) e coletar os primeiros N vídeos
                    &
                    canais.</li>
                <li>2) Para cada canal/vídeo, extrair e normalizar <span class="font-medium">descrições</span> e <span
                        class="font-medium">URLs</span>.</li>
                <li>3) Detectar <span class="font-medium">palavras-chave</span> (pix, carteira, “apoia”, “patreon”,
                    “buymeacoffee”…), <span class="font-medium">domínios</span> e <span
                        class="font-medium">TLDs</span>.
                </li>
                <li>4) Agregar por plataforma e por TLD; produzir séries/contagens e gráficos.</li>
            </ol>
        </div>
        <div class="rounded-2xl ring-1 ring-zinc-200 p-6">
            <h3 class="font-medium">Proxies de monetização nativa (YouTube)</h3>
            <ul class="mt-3 space-y-2 text-zinc-700">
                <li>• Sinais de <span class="font-medium">suitability</span> (ad-friendly vs borderline),
                    demonetizações
                    relatadas.</li>
                <li>• <span class="font-medium">Lives</span> com Super Chat/Thanks; timestamps e volumes (quando
                    públicos).</li>
                <li>• <span class="font-medium">Membros do canal</span> (exposição pública de tiers/benefícios, quando
                    disponível).</li>
                <li>• <span class="font-medium">Frequência de upload</span> e duração média (impacto em receita/CPM
                    estimável por nicho).</li>
            </ul>
        </div>
    </section>

</div>









@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart)
                return;
            const annotationPlugin = window['chartjs-plugin-annotation'];
            if (annotationPlugin)
                Chart.register(annotationPlugin);


            // ======= EXEMPLO DE DADOS (segmento: "aborto") =======
            const exemploAltMonet = [{
                    tag: 'Pix',
                    count: 58,
                    patterns: ['pix', 'chave pix', 'qr pix']
                },
                {
                    tag: 'PicPay',
                    count: 17,
                    patterns: ['picpay.me', 'picpay']
                },
                {
                    tag: 'PayPal',
                    count: 25,
                    patterns: ['paypal.me', 'paypal.com']
                },
                {
                    tag: 'Bitcoin',
                    count: 12,
                    patterns: ['bc1', '1[0-9A-Za-z]{25,}', '3[0-9A-Za-z]{25,}']
                },
                {
                    tag: 'BuyMeACoffee',
                    count: 9,
                    patterns: ['buymeacoffee.com']
                },
                {
                    tag: 'Patreon',
                    count: 6,
                    patterns: ['patreon.com']
                },
                {
                    tag: 'Apoia.se',
                    count: 15,
                    patterns: ['apoia.se']
                },
                {
                    tag: 'Mercado Pago',
                    count: 11,
                    patterns: ['mercadopago', 'mpago.la']
                },
                {
                    tag: 'Vaquinha/Doação',
                    count: 8,
                    patterns: ['vakinha.com.br', 'doar']
                },
            ];

            const exemploTLDs = [{
                    tld: '.br',
                    count: 62
                },
                {
                    tld: '.com',
                    count: 41
                },
                {
                    tld: '.me',
                    count: 15
                },
                {
                    tld: '.io',
                    count: 7
                },
                {
                    tld: '.app',
                    count: 5
                },
            ];

            // ======= GRÁFICO 1: BARRAS HORIZONTAIS (formas/plataformas) =======
            const barCtx = document.getElementById('altMonetBar');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: exemploAltMonet.map(d => d.tag),
                    datasets: [{
                        label: 'Ocorrências em descrições',
                        data: exemploAltMonet.map(d => d.count),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.parsed.x} ocorrências`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            ticks: {
                                autoSkip: false
                            }
                        }
                    }
                }
            });

            // ======= GRÁFICO 2: DOUGHNUT (TLDs) =======
            const donutCtx = document.getElementById('tldDoughnut');
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: exemploTLDs.map(d => d.tld),
                    datasets: [{
                        data: exemploTLDs.map(d => d.count),
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = ctx.parsed;
                                    const pct = total ? ((val / total) * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });






        });
    </script>
@endpush
