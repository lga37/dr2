<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    {{-- INTRO + DEFINIÇÃO --}}
    <section class="grid gap-10 lg:grid-cols-2 lg:items-start">
        <div>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Polarização</h1>
            <p class="mt-4 text-zinc-700">
                Polarização é um <span class="font-medium">processo de separação</span> em polos opostos de opinião,
                identidade ou preferência,
                no qual a <span class="font-medium">distribuição das posições</span> se concentra nas extremidades e
                <span class="font-medium">se reduz</span> a disposição para conciliação. No dia a dia, emerge de
                <em>frames</em> morais,
                identitários e afetivos que <span class="font-medium">organizam o debate em “nós vs. eles”</span>.
            </p>
            <ul class="mt-4 space-y-2 text-zinc-700">
                <li>• <span class="font-medium">Polarização vs. Sentimento:</span> usamos sentimento
                    (positivo/negativo/neutro) como um <em>proxy</em> operacional,
                    mas polarização é mais ampla (pertencimento, antagonismo, clivagens sociais).</li>
                <li>• <span class="font-medium">Foco recente:</span> polarização <em>política</em> (ex.: conservadorismo
                    ↔ progressismo), mas o fenômeno
                    se estende a <em>religiões</em>, <em>clubes de futebol</em>, <em>marcas</em> etc.</li>
            </ul>
        </div>

        {{-- ORGANOGRAMA / TAXONOMIA (SVG) --}}
        <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
            <h3 class="font-medium text-zinc-800 mb-3">Taxonomia ilustrativa de polarização</h3>
            <svg viewBox="0 0 820 380" class="w-full h-auto">
                <defs>
                    <style>
                        .node {
                            fill: #fff;
                            stroke: #e5e7eb;
                            stroke-width: 1.5;
                        }

                        .label {
                            font: 12px system-ui, -apple-system, Segoe UI, Inter, Roboto;
                            fill: #111827;
                        }

                        .sub {
                            fill: #4f46e5;
                            font-weight: 600;
                        }

                        .edge {
                            stroke: #c7d2fe;
                            stroke-width: 2;
                        }
                    </style>
                </defs>
                <!-- raiz -->
                <rect x="320" y="10" rx="10" ry="10" width="180" height="36" class="node" />
                <text x="410" y="33" text-anchor="middle" class="label sub">Polarização (conceito)</text>

                <!-- nível 1 -->
                <line x1="410" y1="46" x2="200" y2="96" class="edge" />
                <line x1="410" y1="46" x2="410" y2="96" class="edge" />
                <line x1="410" y1="46" x2="620" y2="96" class="edge" />

                <rect x="130" y="96" rx="10" ry="10" width="140" height="36" class="node" />
                <text x="200" y="118" text-anchor="middle" class="label">Temática</text>

                <rect x="340" y="96" rx="10" ry="10" width="140" height="36" class="node" />
                <text x="410" y="118" text-anchor="middle" class="label">Forma</text>

                <rect x="550" y="96" rx="10" ry="10" width="140" height="36" class="node" />
                <text x="620" y="118" text-anchor="middle" class="label">Métrica</text>

                <!-- Temática -->
                <line x1="200" y1="132" x2="90" y2="182" class="edge" />
                <line x1="200" y1="132" x2="200" y2="182" class="edge" />
                <line x1="200" y1="132" x2="310" y2="182" class="edge" />

                <rect x="40" y="182" rx="10" ry="10" width="100" height="36" class="node" />
                <text x="90" y="204" text-anchor="middle" class="label">Política</text>
                <rect x="150" y="182" rx="10" ry="10" width="100" height="36" class="node" />
                <text x="200" y="204" text-anchor="middle" class="label">Religiosa</text>
                <rect x="260" y="182" rx="10" ry="10" width="100" height="36" class="node" />
                <text x="310" y="204" text-anchor="middle" class="label">Cultural/Esportiva</text>

                <!-- Forma -->
                <line x1="410" y1="132" x2="360" y2="182" class="edge" />
                <line x1="410" y1="132" x2="460" y2="182" class="edge" />
                <line x1="410" y1="132" x2="410" y2="182" class="edge" />

                <rect x="330" y="182" rx="10" ry="10" width="80" height="36" class="node" />
                <text x="370" y="204" text-anchor="middle" class="label">Afectiva</text>
                <rect x="420" y="182" rx="10" ry="10" width="80" height="36" class="node" />
                <text x="460" y="204" text-anchor="middle" class="label">Ideológica</text>
                <rect x="375" y="232" rx="10" ry="10" width="70" height="32" class="node" />
                <text x="410" y="253" text-anchor="middle" class="label">Identitária</text>

                <!-- Métrica -->
                <line x1="620" y1="132" x2="570" y2="182" class="edge" />
                <line x1="620" y1="132" x2="620" y2="182" class="edge" />
                <line x1="620" y1="132" x2="670" y2="182" class="edge" />

                <rect x="530" y="182" rx="10" ry="10" width="80" height="36" class="node" />
                <text x="570" y="204" text-anchor="middle" class="label">Sentimento</text>
                <rect x="610" y="182" rx="10" ry="10" width="80" height="36" class="node" />
                <text x="650" y="204" text-anchor="middle" class="label">Linguagem</text>
                <rect x="690" y="182" rx="10" ry="10" width="90" height="36" class="node" />
                <text x="735" y="204" text-anchor="middle" class="label">Rede/Clusters</text>
            </svg>
            <p class="mt-3 text-xs text-zinc-500">
                Ilustração: “Temática” (onde ocorre), “Forma” (como se manifesta) e “Métrica” (como medimos: sentimento,
                linguagem, estrutura de rede).
            </p>
        </div>
    </section>

    {{-- LLMs + LIKERT --}}
    <section class="mt-14 grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
            <h3 class="text-lg font-semibold">Classificação com LLMs (Likert político)</h3>
            <p class="mt-3 text-zinc-700">
                Para <span class="font-medium">segmentar</span> conteúdo, podemos pedir a uma LLM uma <span
                    class="font-medium">escala Likert</span>
                de posicionamento político (por exemplo: Esquerda radical, Esquerda, Neutro, Direita, Direita radical),
                com justificativa curta e confiança. Útil para validar hipóteses e comparar com as métricas
                automatizadas.
            </p>
            <div class="mt-4 rounded-xl bg-zinc-50 ring-1 ring-zinc-200 p-4 text-sm">
                <div class="text-xs uppercase tracking-wider text-zinc-500 mb-1">Prompt base (exemplo)</div>
                <pre class="whitespace-pre-wrap text-zinc-800">
Analise o texto abaixo (título/descrição/transcrição). Classifique em uma escala:
[Esquerda radical, Esquerda, Neutro, Direita, Direita radical].
Retorne JSON: {"classe":&lt;string&gt;,"justificativa":&lt;string&gt;,"confianca":0..1}.
Texto: &lt;COLAR AQUI&gt;</pre>
            </div>
        </article>

        <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
            <h3 class="text-lg font-semibold">Indicadores práticos</h3>
            <ul class="mt-3 space-y-2 text-zinc-700">
                <li>• <span class="font-medium">Sentimento</span>: positivo/negativo/neutro em títulos, descrições e
                    comentários.</li>
                <li>• <span class="font-medium">Linguagem polarizadora</span>: marcadores de “nós vs. eles”, rótulos de
                    out-group, frames morais.</li>
                <li>• <span class="font-medium">Estrutura de rede</span>: modularidade, comunidades e fluxo de
                    audiência entre canais.</li>
            </ul>
        </article>
    </section>

    {{-- NUVEM (2/3) + HISTOGRAMA (1/3) --}}
    <section class="mt-14">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold">Vocabulário de um canal fictício (segmento: aborto)</h2>
            <div class="text-sm text-zinc-500">Exemplo didático (dados simulados)</div>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-3">
            <!-- Wordcloud ocupa 2/3 -->
            <div class="lg:col-span-2 bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Nuvem de palavras</h3>
                    <span class="text-xs text-zinc-500">Chart.js + plugin de WordCloud</span>
                </div>
                <div class="relative" style="height:420px">
                    <canvas id="wcPolarizacao"></canvas>
                </div>
            </div>

            <!-- Histograma 1/3 -->
            <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Frequência das palavras</h3>
                    <span class="text-xs text-zinc-500">Top termos do canal fictício</span>
                </div>
                <div class="relative" style="height:420px">
                    <canvas id="histPolarizacao"></canvas>
                </div>
            </div>
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





            // ====== Dados fictícios (canal sobre "aborto") ======
            const termos = [{
                    text: 'aborto',
                    weight: 48
                },
                {
                    text: 'direitos',
                    weight: 28
                },
                {
                    text: 'saúde',
                    weight: 26
                },
                {
                    text: 'vida',
                    weight: 22
                },
                {
                    text: 'legal',
                    weight: 20
                },
                {
                    text: 'religião',
                    weight: 18
                },
                {
                    text: 'autonomia',
                    weight: 16
                },
                {
                    text: 'feto',
                    weight: 15
                },
                {
                    text: 'ético',
                    weight: 14
                },
                {
                    text: 'criminalização',
                    weight: 12
                },
                {
                    text: 'acesso',
                    weight: 12
                },
                {
                    text: 'médico',
                    weight: 11
                },
                {
                    text: 'violência',
                    weight: 10
                },
                {
                    text: 'saúde pública',
                    weight: 10
                },
                {
                    text: 'consciência',
                    weight: 9
                },
                {
                    text: 'conservador',
                    weight: 9
                },
                {
                    text: 'progressista',
                    weight: 9
                },
                {
                    text: 'política',
                    weight: 8
                },
                {
                    text: 'jurídico',
                    weight: 8
                },
                {
                    text: 'segurança',
                    weight: 7
                },
                {
                    text: 'risco',
                    weight: 7
                },
                {
                    text: 'estupro',
                    weight: 6
                },
                {
                    text: 'planejamento',
                    weight: 6
                },
                {
                    text: 'direito penal',
                    weight: 5
                },
            ];

            // ====== WordCloud (2/3) ======
            const wcCtx = document.getElementById('wcPolarizacao');
            new Chart(wcCtx, {
                type: 'wordCloud',
                data: {
                    // rótulos e pesos
                    labels: termos.map(t => t.text),
                    datasets: [{
                        label: 'Wordcloud',
                        data: termos.map(t => t.weight),
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${ctx.raw}`
                            }
                        }
                    },
                    elements: {
                        word: {
                            // tamanho mínimo/máximo da fonte em px
                            minFontSize: 12,
                            maxFontSize: 64,
                            // rotação leve aleatória
                            rotate: () => (Math.random() > 0.8 ? 90 : 0),
                        }
                    },
                    layoutPadding: 10
                }
            });

            // ====== Histograma (1/3) – top N termos ======
            const topN = [...termos].sort((a, b) => b.weight - a.weight).slice(0, 12);
            const histCtx = document.getElementById('histPolarizacao');
            new Chart(histCtx, {
                type: 'bar',
                data: {
                    labels: topN.map(t => t.text),
                    datasets: [{
                        label: 'Ocorrências',
                        data: topN.map(t => t.weight),
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (c) => `${c.parsed.y} ocorrências`
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });



        });
    </script>
@endpush
