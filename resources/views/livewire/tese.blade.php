<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    {{-- HERO --}}
    <section class="text-center">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Tese — YouTube: Monetização, Toxicidade e Polarização
        </h1>
        <p class="mt-4 text-zinc-600 max-w-3xl mx-auto">
            Resumo executivo da pesquisa e do sistema automatizado (RPA) que conecta <span
                class="font-medium">monetização</span>,
            <span class="font-medium">toxicidade</span> e <span class="font-medium">polarização</span> em vídeos e canais
            do YouTube.
        </p>
    </section>

    {{-- SEÇÃO 1: VISÃO-GERAL (texto + Venn) --}}
    <section class="mt-12 grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="order-2 lg:order-1">
            <h2 class="text-2xl font-semibold">Visão geral</h2>
            <p class="mt-3 text-zinc-700">
                A tese parte de três pilares que frequentemente interagem no ecossistema do YouTube. Em conjunto,
                eles formam um <span class="font-medium">ponto de intersecção</span> onde incentivos econômicos,
                dinâmicas sociais e linguagem hostil se reforçam mutuamente. É justamente neste ponto (∩ Monetização,
                Toxicidade e
                Polarização) que situamos a <span class="font-medium">principal contribuição</span> — um arcabouço
                empírico e
                tecnológico para medir e explicar esses efeitos.
            </p>
            <ul class="mt-5 space-y-2 text-zinc-700">
                <li>• <span class="font-medium">Monetização:</span> ads, memberships, Super Chat, patrocínios.</li>
                <li>• <span class="font-medium">Toxicidade:</span> linguagem hostil/ofensiva em comentários e conteúdo.
                </li>
                <li>• <span class="font-medium">Polarização:</span> alinhamento grupal, antagonismo e eco chambers.</li>
            </ul>
        </div>

        {{-- Venn diagram SVG --}}
        <div class="order-1 lg:order-2">
            <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">




                <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-zinc-800">Mapa conceitual (Venn)</h3>
                        <div class="text-xs text-zinc-500">Intersecções com cores distintas</div>
                    </div>
                    <div class="relative" style="height:380px">
                        <canvas id="vennChart"></canvas>
                    </div>
                </div>







            </div>
        </div>
    </section>

    {{-- SEÇÃO 2: CONTRIBUIÇÃO CENTRAL --}}
    <section class="mt-14">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="bg-indigo-50 rounded-2xl p-6 ring-1 ring-indigo-100">
                <h3 class="text-indigo-900 font-semibold">Contribuição Acadêmica (∩ Monetização ∩ Toxicidade ∩
                    Polarização)</h3>
                <ul class="mt-3 space-y-2 text-indigo-900/80">
                    <li>• Modelo explicativo de <span class="font-medium">ciclos de reforço</span> (incentivo econômico
                        → engajamento → linguagem hostil → identidade grupal → retorno financeiro).</li>
                    <li>• Protocolo de medição reprodutível com <span class="font-medium">métricas padronizadas</span> e
                        recortes amostrais (POIs).</li>
                    <li>• Evidências empíricas em séries temporais (vídeos, canais, comentários e eventos de
                        monetização).</li>
                </ul>
            </div>
            <div class="bg-indigo-50 rounded-2xl p-6 ring-1 ring-indigo-100">
                <h3 class="text-indigo-900 font-semibold">Contribuição Tecnológica (RPA end-to-end)</h3>
                <ul class="mt-3 space-y-2 text-indigo-900/80">
                    <li>• <span class="font-medium">Robotic Process Automation</span> para coleta contínua (webcrawling
                        + APIs).</li>
                    <li>• Pipeline de NLP (tox scores, sentimento, tópicos, embeddings), dashboards e widgets para
                        pesquisadores.</li>
                    <li>• Reprodutibilidade: versionamento de consultas, <span class="font-medium">POIs</span> e
                        snapshots de páginas (WebArchive).</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- SEÇÃO 3: INTERSECÇÕES PAR-A-PAR (cards) --}}
    <section class="mt-14">
        <h2 class="text-2xl font-semibold">Intersecções específicas (pares)</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Monetização ∩ Toxicidade -->
            <article class="rounded-2xl ring-1 ring-emerald-100 bg-emerald-50 p-6">
                <h3 class="font-semibold text-emerald-900">Monetização ∩ Toxicidade</h3>
                <p class="mt-2 text-emerald-900/80 text-sm">
                    Conteúdos <em>borderline</em> podem elevar CTR e tempo de exibição, mas flertam com
                    <span class="font-medium">inadmissibilidade de anúncios</span> e ciclos de “outrage”. A análise
                    observa CPM, demonetizações,
                    volume de ofensas e termos sensíveis vs. retenção.
                </p>
            </article>

            <!-- Toxicidade ∩ Polarização -->
            <article class="rounded-2xl ring-1 ring-rose-100 bg-rose-50 p-6">
                <h3 class="font-semibold text-rose-900">Toxicidade ∩ Polarização</h3>
                <p class="mt-2 text-rose-900/80 text-sm">
                    Escalada de ofensas reforça identidades de grupo e <span class="font-medium">echo chambers</span>.
                    Medimos densidade de ataques, alvo (out-group vs in-group), e variação temporal após eventos gatilho
                    (vídeos, lives, notícias).
                </p>
            </article>

            <!-- Polarização ∩ Monetização -->
            <article class="rounded-2xl ring-1 ring-blue-100 bg-blue-50 p-6">
                <h3 class="font-semibold text-blue-900">Polarização ∩ Monetização</h3>
                <p class="mt-2 text-blue-900/80 text-sm">
                    Nichos polarizados geram <span class="font-medium">receita recorrente</span> (memberships, Super
                    Chat, produtos de afiliação)
                    com forte alinhamento identitário. Observamos picos de receita vs. retórica de nós/eles e chamadas à
                    ação.
                </p>
            </article>
        </div>
    </section>

    {{-- SEÇÃO 4: PIPELINE RPA (alternando layout) --}}
    <section class="mt-16 grid gap-10 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-2xl font-semibold">Pipeline automatizado (RPA)</h2>
            <ol class="mt-4 space-y-3 text-zinc-700">
                <li>1) <span class="font-medium">POIs</span> (pontos de interesse): queries, temas, eventos,
                    canais-semente.</li>
                <li>2) Coleta: YouTube API, scraping complementar (vidIQ/SocialBlade), WebArchive, metadados.</li>
                <li>3) Normalização: dedupe, enriquecimento, vinculação vídeo↔canal↔evento.</li>
                <li>4) NLP: <span class="font-medium">toxicity scoring</span>, sentimento, tópicos/embeddings, detecção
                    de frames polarizadores.</li>
                <li>5) Séries temporais: janelas móveis, antes/depois de eventos, testes de robustez.</li>
                <li>6) Dashboards & widgets: consultas exploratórias, export e <span
                        class="font-medium">reprodutibilidade</span>.</li>
            </ol>
        </div>
        <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Fontes</dt>
                    <dd class="text-zinc-800 mt-1">YouTube API, WebArchive, scraping auxiliar</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">NLP</dt>
                    <dd class="text-zinc-800 mt-1">Toxicidade, sentimento, tópicos, embeddings</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Escala</dt>
                    <dd class="text-zinc-800 mt-1">Coleta contínua (cron) e por eventos</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">Outputs</dt>
                    <dd class="text-zinc-800 mt-1">Tabelas normalizadas + gráficos</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- SEÇÃO 5: POIs e MÉTRICAS --}}
    <section class="mt-16">
        <h2 class="text-2xl font-semibold">POIs e métricas-chave</h2>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl ring-1 ring-zinc-200 p-6">
                <h3 class="font-medium">POIs (Pontos de Interesse)</h3>
                <ul class="mt-3 text-zinc-700 space-y-2">
                    <li>• Temas/surtos noticiosos (eleições, casos virais, crises).</li>
                    <li>• Canais âncora de nichos polarizados.</li>
                    <li>• Eventos de monetização (lives com Super Chat, campanhas de membros).</li>
                </ul>
            </div>
            <div class="rounded-2xl ring-1 ring-zinc-200 p-6">
                <h3 class="font-medium">Métricas</h3>
                <ul class="mt-3 text-zinc-700 space-y-2">
                    <li>• <span class="font-medium">Monetização:</span> proxies de receita (ads suitability, super
                        chats, memberships), frequência de uploads.</li>
                    <li>• <span class="font-medium">Toxicidade:</span> score médio/percentis, alvos, termos sensíveis,
                        evolução temporal.</li>
                    <li>• <span class="font-medium">Polarização:</span> marcadores nós/eles, alinhamento por tópico,
                        clusterização de audiência.</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- SEÇÃO 6: CTA / Próximos passos --}}
    <section class="mt-16">
        <div class="rounded-2xl ring-1 ring-zinc-200 p-8 bg-gradient-to-br from-zinc-50 to-white">
            <h2 class="text-xl font-semibold">Próximos passos</h2>
            <p class="mt-2 text-zinc-700">
                Expandir POIs, validar robustez dos modelos de NLP e publicar o conjunto de dados e widgets de
                exploração.
                Na sequência, aplicar o framework a outras plataformas e comparar regimes de monetização.
            </p>
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


            const VennPlugin = {
                id: 'venn',
                afterDraw(chart) {
                    const ctx = chart.ctx;
                    const ca = chart.chartArea;
                    if (!ca) return;

                    const W = ca.right - ca.left;
                    const H = ca.bottom - ca.top;

                    // raio levemente menor p/ dar respiro
                    const r = Math.min(W, H) / 3.25;

                    // posições iniciais
                    const C = [{
                            key: 'M',
                            x: ca.left + W * 0.35,
                            y: ca.top + H * 0.44,
                            r
                        }, // Monetização
                        {
                            key: 'T',
                            x: ca.left + W * 0.65,
                            y: ca.top + H * 0.44,
                            r
                        }, // Toxicidade
                        {
                            key: 'P',
                            x: ca.left + W * 0.50,
                            y: ca.top + H * 0.71,
                            r
                        }, // Polarização
                    ];

                    // >>> NOVO: auto-lift para evitar tocar o rodapé <<<
                    const bottomMost = Math.max(...C.map(c => c.y + c.r));
                    const paddingBottom = 10; // px de respiro
                    const overflow = bottomMost - (ca.bottom - paddingBottom);
                    if (overflow > 0) C.forEach(c => c.y -= overflow);

                    // ------ (segue igual abaixo) ------
                    const base = ['rgba(16,185,129,0.25)', 'rgba(239,68,68,0.25)', 'rgba(59,130,246,0.25)'];
                    const strokes = ['#059669', '#b91c1c', '#1d4ed8'];
                    const pair = ['rgba(16,185,129,0.45)', 'rgba(239,68,68,0.45)', 'rgba(59,130,246,0.45)'];
                    const triple = 'rgba(99,102,241,0.80)';

                    const drawCircle = (context, c) => {
                        context.beginPath();
                        context.arc(c.x, c.y, c.r, 0, Math.PI * 2);
                        context.closePath();
                    };

                    const compose = (steps) => {
                        const buf = document.createElement('canvas');
                        buf.width = chart.width;
                        buf.height = chart.height;
                        const btx = buf.getContext('2d');
                        steps(btx);
                        ctx.save();
                        ctx.globalCompositeOperation = 'source-over';
                        ctx.drawImage(buf, 0, 0);
                        ctx.restore();
                    };

                    // bases
                    C.forEach((c, i) => {
                        ctx.save();
                        ctx.fillStyle = base[i];
                        drawCircle(ctx, c);
                        ctx.fill();
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = strokes[i];
                        ctx.stroke();
                        ctx.restore();
                    });

                    // pares
                    compose(btx => {
                        btx.fillStyle = pair[0];
                        drawCircle(btx, C[0]);
                        btx.fill();
                        btx.globalCompositeOperation = 'destination-in';
                        drawCircle(btx, C[1]);
                        btx.fill();
                    });
                    compose(btx => {
                        btx.fillStyle = pair[1];
                        drawCircle(btx, C[1]);
                        btx.fill();
                        btx.globalCompositeOperation = 'destination-in';
                        drawCircle(btx, C[2]);
                        btx.fill();
                    });
                    compose(btx => {
                        btx.fillStyle = pair[2];
                        drawCircle(btx, C[0]);
                        btx.fill();
                        btx.globalCompositeOperation = 'destination-in';
                        drawCircle(btx, C[2]);
                        btx.fill();
                    });

                    // tripla
                    compose(btx => {
                        btx.fillStyle = triple;
                        drawCircle(btx, C[0]);
                        btx.fill();
                        btx.globalCompositeOperation = 'destination-in';
                        drawCircle(btx, C[1]);
                        btx.fill();
                        drawCircle(btx, C[2]);
                        btx.fill();
                    });

                    // labels (recalculadas com Y ajustado)
                    ctx.save();
                    ctx.fillStyle = '#111827';
                    ctx.font = '500 12px system-ui, -apple-system, Segoe UI, Inter, Roboto';
                    ctx.fillText('Monetização', C[0].x - 46, C[0].y - C[0].r - 8);
                    ctx.fillText('Toxicidade', C[1].x - 36, C[1].y - C[1].r - 8);
                    ctx.fillText('Polarização', C[2].x - 40, C[2].y + C[2].r + 16);
                    ctx.font = '600 12px system-ui, -apple-system, Segoe UI, Inter, Roboto';
                    const cx = (C[0].x + C[1].x + C[2].x) / 3;
                    const cy = (C[0].y + C[1].y + C[2].y) / 3;
                    ctx.fillText('Contribuição da Tese', cx - 70, cy + 4);
                    ctx.restore();
                }
            };

            // registro/instância permanecem os mesmos
            Chart.register(VennPlugin);
            new Chart(document.getElementById('vennChart'), {
                type: 'scatter',
                data: {
                    datasets: []
                },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    },
                    layout: {
                        padding: 10
                    }
                }
            });



        });
    </script>
@endpush
