<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    {{-- INTRO --}}
    <section class="grid gap-10 lg:grid-cols-2 lg:items-start">
        <div>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Toxicidade</h1>
            <p class="mt-4 text-zinc-700">
                No YouTube, os <span class="font-medium">comentários são abertos</span> — não exigem vínculo prévio com o
                criador.
                Por isso, entender <span class="font-medium">como a audiência reage</span> é central para analisar o
                ecossistema:
                volume, <em>timing</em> de picos e grau de linguagem hostil.
            </p>
            <p class="mt-3 text-zinc-700">
                Utilizamos principalmente a <span class="font-medium">Perspective API</span> (Google Jigsaw) para obter
                <em>scores</em> de probabilidade de toxicidade em comentários (e.g., <code>TOXICITY</code>,
                <code>INSULT</code>, <code>THREAT</code>),
                complementando com contagem/frequência de termos e nuvens de palavras quando apropriado.
            </p>
        </div>

        {{-- CARDS SOBRE A ABORDAGEM --}}
        <div class="grid gap-6">
            <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
                <h3 class="text-lg font-semibold">Perspective API — como usamos</h3>
                <ul class="mt-3 space-y-2 text-zinc-700">
                    <li>• Entrada: texto de comentários (pré-processado: limpeza básica, idioma).</li>
                    <li>• Saída: escores 0–1 (probabilidade de toxicidade por atributo).</li>
                    <li>• Agregação: médias, percentis e janelas móveis por vídeo/canal/segmento.</li>
                    <li>• Interpretação: thresholds (ex.: ≥0.7 como “alto risco”) configuráveis.</li>
                </ul>
                <p class="mt-3 text-xs text-zinc-500">
                    Limitações: viés de classificação, contexto cultural/ironia, dependência de idioma. Tratamos com
                    amostragem balanceada,
                    revisão humana pontual e comparação com métricas auxiliares (termos e co-ocorrências).
                </p>
            </article>

            <article class="rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
                <h3 class="text-lg font-semibold">Métricas derivadas</h3>
                <ul class="mt-3 space-y-2 text-zinc-700">
                    <li>• <span class="font-medium">Toxicidade média</span> por janela (ex.: 10min/1h).</li>
                    <li>• <span class="font-medium">Hateful comments/s</span> (comentários acima do threshold por
                        segundo/minuto).</li>
                    <li>• <span class="font-medium">Percentis</span> (Q1, mediana, Q3) e share de alto risco (≥0.7).
                    </li>
                    <li>• <span class="font-medium">Eventos</span>: lançamento do vídeo, cortes, repercussões externas
                        (correlacionáveis com monetização e audiência).</li>
                </ul>
            </article>
        </div>
    </section>

    {{-- GRÁFICO: TOXICIDADE AO LONGO DO TEMPO --}}
    <section class="mt-14">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold">Evolução temporal da toxicidade</h2>
            <div class="text-sm text-zinc-500">Exemplo didático (dados simulados)</div>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Score médio por janela (0–100%)</h3>
                    <span class="text-xs text-zinc-500">Linha vertical marca a publicação do vídeo</span>
                </div>
                <div class="relative" style="height:360px">
                    <canvas id="toxLine"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl ring-1 ring-zinc-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-zinc-800">Resumo do período</h3>
                    <span class="text-xs text-zinc-500">Q1 • Mediana • Q3</span>
                </div>
                <div class="relative" style="height:360px">
                    <canvas id="toxBox"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- BLOCO TÉCNICO: CHAMADA PERSPECTIVE (opcional) --}}
    <section class="mt-14 rounded-2xl ring-1 ring-zinc-200 bg-white p-6">
        <h3 class="text-lg font-semibold">Exemplo de chamada à Perspective API (PHP/Laravel)</h3>
        <p class="mt-2 text-zinc-700 text-sm">Esqueleto simples para obter <code>TOXICITY</code> (0–1) de um comentário.
        </p>
        <pre class="mt-4 bg-zinc-50 rounded-lg p-4 text-xs overflow-auto"><code>use Illuminate\Support\Facades\Http;

$apiKey = config('services.perspective.key');
$endpoint = "https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key={$apiKey}";

$payload = [
  'comment' => ['text' => $texto],
  'languages' => ['pt'], // ou ['pt','en'] se misto
  'requestedAttributes' => ['TOXICITY' => new \stdClass()],
  'doNotStore' => true,
];

$res = Http::post($endpoint, $payload)->json();
$score = data_get($res, 'attributeScores.TOXICITY.summaryScore.value'); // 0..1
</code></pre>
        <p class="mt-2 text-xs text-zinc-500">
            Observação: trate limites de quota, tempo de resposta e backoff. Armazene apenas escores agregados quando
            possível.
        </p>
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


            // ======== DADOS FICTÍCIOS ========
            const minutes = 72; // 6h em janelas de 5min (72 pontos)
            const stepMin = 5;
            const labels = Array.from({
                length: minutes
            }, (_, i) => i * stepMin); // minutos desde T0
            const publishIndex = 2; // ~10min após T0
            const series = labels.map((m, i) => {
                // pico inicial decaindo
                const base = i < 12 ? 0.45 + 0.4 * Math.exp(-i / 3) : 0.18 + 0.06 * Math.sin(i / 6);
                // ruído suave
                return Math.max(0, Math.min(1, base + (Math.random() - 0.5) * 0.06));
            });

            // calculinhos
            const pct = (arr, p) => {
                const a = [...arr].sort((x, y) => x - y),
                    k = (a.length - 1) * p,
                    f = Math.floor(k),
                    c = Math.ceil(k);
                return f === c ? a[k] : a[f] + (a[c] - a[f]) * (k - f);
            };
            const q1 = pct(series, .25),
                med = pct(series, .5),
                q3 = pct(series, .75);

            // ======== GRÁFICO 1: LINHA ========
            const toxCtx = document.getElementById('toxLine');
            new Chart(toxCtx, {
                type: 'line',
                data: {
                    labels: labels.map(m => `${m} min`),
                    datasets: [{
                        label: 'Toxicidade média (janela de 5 min)',
                        data: series.map(v => Math.round(v * 100)),
                        pointRadius: 0,
                        tension: .35,
                        borderWidth: 2
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
                                label: c => `${c.parsed.y}%`
                            }
                        },
                        annotation: {
                            annotations: {
                                t0: {
                                    type: 'line',
                                    xMin: publishIndex,
                                    xMax: publishIndex,
                                    borderWidth: 1,
                                    borderDash: [4, 4],
                                    label: {
                                        display: true,
                                        content: 'Publicação',
                                        position: 'start'
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true
                            }
                        }
                    }
                },
                plugins: []
            });

            // ======== GRÁFICO 2: “Boxplot” simples (barras) ========
            const boxCtx = document.getElementById('toxBox');
            new Chart(boxCtx, {
                type: 'bar',
                data: {
                    labels: ['Q1', 'Mediana', 'Q3'],
                    datasets: [{
                        data: [q1, med, q3].map(v => Math.round(v * 100)),
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%'
                            }
                        }
                    }
                }
            });






        });
    </script>
@endpush
