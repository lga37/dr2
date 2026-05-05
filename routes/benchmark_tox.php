<?php

#use App\Livewire\Monet;
use App\Http\Controllers\ProfileController;
use App\Livewire\Arxiv;
use App\Livewire\Busca;
use App\Livewire\Canal;
use App\Livewire\Comentario;
use App\Livewire\Graf;
use App\Livewire\Monet;
use App\Livewire\Monetizacao;
use App\Livewire\Nlp;
use App\Livewire\Polarizacao;
use App\Livewire\Resultados;
use App\Livewire\Tarefa1;
use App\Livewire\Tarefa2;
use App\Livewire\Tarefa3;
use App\Livewire\Tarefa4;
use App\Livewire\Tese;
use App\Livewire\Toxic;
use App\Livewire\Toxicidade;
use App\Livewire\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;


################################# INICIO BENCHMARK ####################################
#######################################################################################



if (!function_exists('benchmarkDetoxify')) {
    function benchmarkDetoxify(string $txt, string $model = 'multilingual'): ?array
    {
        $python = base_path('storage/app/detoxify-venv/bin/python');
        $script = base_path('app/Scripts/detoxify_predict.py');

        $payload = json_encode([
            'text' => $txt,
            'model' => $model,
        ], JSON_UNESCAPED_UNICODE);

        $process = new Process([$python, $script]);
        $process->setInput($payload);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('Detoxify error', [
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
            ]);
            return null;
        }

        $json = json_decode($process->getOutput(), true);

        if (!is_array($json) || isset($json['error'])) {
            Log::warning('Detoxify invalid response', ['response' => $process->getOutput()]);
            return null;
        }

        return $json['result'] ?? null;
    }
}


if (!function_exists('benchCurl')) {
    function benchCurl(string $url, ?string $postFields = null, ?array $headers = null, int $timeout = 25): ?string
    {
        $ch = curl_init($url);

        if (!empty($postFields)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
        ]);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::warning('benchCurl error', ['error' => curl_error($ch)]);
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        return $data ?: null;
    }
}

if (!function_exists('benchmarkPerspective')) {
    function benchmarkPerspective(string $txt): ?float
    {
        $apiKey = env('PERSPECTIVE_API');

        if (!$apiKey) {
            return null;
        }

        $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . $apiKey;

        $payload = [
            'comment' => ['text' => $txt],
            'languages' => ['pt', 'en'],
            'requestedAttributes' => [
                'TOXICITY' => new stdClass(),
            ],
        ];

        $response = benchCurl(
            $url,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            ['Content-Type: application/json'],
            25
        );

        $res = json_decode((string) $response, true);

        return isset($res['attributeScores']['TOXICITY']['summaryScore']['value'])
            ? round((float) $res['attributeScores']['TOXICITY']['summaryScore']['value'], 3)
            : null;
    }
}

if (!function_exists('benchmarkGoogleNLP')) {
    function benchmarkGoogleNLP(string $texto): array
    {
        $texto = trim($texto);

        $empty = [
            'raw' => null,
            'amp' => null,
            'score' => null,
            'magnitude' => null,
        ];

        if ($texto === '') {
            return $empty;
        }

        if (mb_strlen($texto) > 3000) {
            $texto = mb_substr($texto, 0, 3000);
        }

        $apiKey = env('GOOGLE_API_KEY');

        if (!$apiKey) {
            return $empty;
        }

        $url = 'https://language.googleapis.com/v1/documents:analyzeSentiment?key=' . $apiKey;

        $payload = [
            'document' => [
                'type' => 'PLAIN_TEXT',
                'content' => $texto,
            ],
            'encodingType' => 'UTF8',
        ];

        try {
            $response = Http::timeout(25)->post($url, $payload);

            if (!$response->successful()) {
                Log::warning('Google NLP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $empty;
            }

            $data = $response->json();

            $score = $data['documentSentiment']['score'] ?? null;
            $magnitude = $data['documentSentiment']['magnitude'] ?? 0;

            if (!is_numeric($score)) {
                return $empty;
            }


            /*
             * Google NLP mede sentimento, não toxicidade.
             * Como proxy comparativa:
             * - raw = negatividade pura em 0..1
             * - amp = negatividade ponderada pela magnitude emocional
             */
            // $score = (float) $score;
            // $magnitude = is_numeric($magnitude) ? (float) $magnitude : 0.0;
            // $raw = max(0, -$score);
            // $amp = $raw * min(1, $magnitude);

            // return [
            //     'raw' => round($raw, 3),
            //     'amp' => round($amp, 3),
            //     'score' => round($score, 3),
            //     'magnitude' => round($magnitude, 3),
            // ];



            $score = (float) $score;
            $magnitude = is_numeric($magnitude) ? (float) $magnitude : 0.0;

            $gtp = max(0, -$score);
            $gtpp = $gtp * min(1, $magnitude);

            return [
                'gss' => round($score, 3),
                'gsm' => round($magnitude, 3),
                'gtp' => round($gtp, 3),
                'gtpp' => round($gtpp, 3),
            ];




        } catch (Throwable $e) {
            Log::error('Google NLP exception', ['msg' => $e->getMessage()]);
            return $empty;
        }
    }
}

if (!function_exists('benchmarkGpt')) {
    function benchmarkGpt(string $txt): ?float
    {
        $key = env('OPENAI_API_KEY');

        if (!$key) {
            return null;
        }

        $payload = [
            'model' => env('BENCHMARK_OPENAI_MODEL', 'gpt-4o-mini'),
            'temperature' => (float) env('BENCHMARK_OPENAI_TEMPERATURE', 0.3),
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um classificador de toxicidade. Responda apenas JSON válido.',
                ],
                [
                    'role' => 'user',
                    'content' =>
                        "Classifique a probabilidade deste comentário ser tóxico em escala contínua de 0 a 1.\n" .
                        "Considere insulto, agressividade, humilhação, hostilidade, ameaça, deboche ofensivo, palavrões e ataques pessoais.\n" .
                        "Retorne apenas JSON no formato: {\"toxicity\": 0.00}\n\n" .
                        "Comentário: {$txt}",
                ],
            ],
        ];

        $response = benchCurl(
            'https://api.openai.com/v1/chat/completions',
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ],
            35
        );

        $json = json_decode((string) $response, true);
        $content = $json['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return null;
        }

        $parsed = json_decode($content, true);

        if (!is_array($parsed) || !isset($parsed['toxicity'])) {
            return null;
        }

        $tox = max(0, min(1, (float) $parsed['toxicity']));

        return round($tox, 3);
    }
}

if (!function_exists('benchmarkDataset')) {
    function benchmarkDataset(): array
    {
        return [
            // Politica negativa
            ['tema' => 'Politica', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Discordo da sua análise política, mas acho que o debate merece argumentos melhores.', 'en' => 'I disagree with your political analysis, but I think the debate deserves better arguments.'],
            ['tema' => 'Politica', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Sua posição política parece equivocada e ignora dados importantes sobre o tema.', 'en' => 'Your political position seems misguided and ignores important data about the topic.'],
            ['tema' => 'Politica', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Você está falando besteira sobre política e simplificando um problema complexo.', 'en' => 'You are talking nonsense about politics and oversimplifying a complex problem.'],
            ['tema' => 'Politica', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Você só fala merda de política e ainda tenta parecer especialista.', 'en' => 'You only talk shit about politics and still try to sound like an expert.'],
            ['tema' => 'Politica', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Você é um idiota político completo, sua opinião é lixo e não serve para nada.', 'en' => 'You are a complete political idiot, your opinion is trash and worth nothing.'],

            // Politica positiva
            ['tema' => 'Politica', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Gostei da forma como você apresentou sua posição política com respeito.', 'en' => 'I liked the way you presented your political position respectfully.'],
            ['tema' => 'Politica', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Seu comentário trouxe uma boa contribuição para o debate político.', 'en' => 'Your comment made a good contribution to the political debate.'],
            ['tema' => 'Politica', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'A análise política foi clara, equilibrada e ajudou a entender melhor o tema.', 'en' => 'The political analysis was clear, balanced, and helped me understand the topic better.'],
            ['tema' => 'Politica', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Excelente comentário, você explicou bem uma questão política difícil.', 'en' => 'Excellent comment, you explained a difficult political issue well.'],
            ['tema' => 'Politica', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Sua análise política foi brilhante, respeitosa e muito bem fundamentada.', 'en' => 'Your political analysis was brilliant, respectful, and very well grounded.'],

            // Religiao negativa
            ['tema' => 'Religiao', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Não concordo com sua visão religiosa, mas entendo que ela seja importante para você.', 'en' => 'I do not agree with your religious view, but I understand that it is important to you.'],
            ['tema' => 'Religiao', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Seu argumento religioso é fraco quando tenta definir política pública para todos.', 'en' => 'Your religious argument is weak when it tries to define public policy for everyone.'],
            ['tema' => 'Religiao', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Você usa religião para fugir de um debate sério e baseado em dados.', 'en' => 'You use religion to avoid a serious debate based on data.'],
            ['tema' => 'Religiao', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Seu comentário religioso é burro, cruel e completamente fora da realidade.', 'en' => 'Your religious comment is stupid, cruel, and completely disconnected from reality.'],
            ['tema' => 'Religiao', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Você é um fanático ignorante usando Deus para justificar sofrimento e humilhação.', 'en' => 'You are an ignorant fanatic using God to justify suffering and humiliation.'],

            // Religiao positiva
            ['tema' => 'Religiao', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Acho importante que pessoas religiosas participem do debate com respeito.', 'en' => 'I think it is important for religious people to participate in the debate respectfully.'],
            ['tema' => 'Religiao', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Seu comentário mostrou uma visão religiosa sensível e cuidadosa.', 'en' => 'Your comment showed a sensitive and thoughtful religious view.'],
            ['tema' => 'Religiao', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Você conseguiu apresentar sua fé sem atacar quem pensa diferente.', 'en' => 'You managed to present your faith without attacking those who think differently.'],
            ['tema' => 'Religiao', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Foi uma contribuição religiosa respeitosa, equilibrada e muito humana.', 'en' => 'It was a respectful, balanced, and very humane religious contribution.'],
            ['tema' => 'Religiao', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Sua fala foi profundamente empática e mostrou como a fé pode acolher pessoas.', 'en' => 'Your statement was deeply empathetic and showed how faith can welcome people.'],

            // Ciencia negativa
            ['tema' => 'Ciencia', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Acho que sua explicação científica precisa de mais dados e menos opinião.', 'en' => 'I think your scientific explanation needs more data and less opinion.'],
            ['tema' => 'Ciencia', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Você interpretou mal o estudo e chegou a uma conclusão fraca.', 'en' => 'You misinterpreted the study and reached a weak conclusion.'],
            ['tema' => 'Ciencia', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Você está espalhando desinformação científica com muita confiança.', 'en' => 'You are spreading scientific misinformation with too much confidence.'],
            ['tema' => 'Ciencia', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Que explicação ridícula, você claramente não entende nada de ciência.', 'en' => 'What a ridiculous explanation, you clearly understand nothing about science.'],
            ['tema' => 'Ciencia', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Você é um completo imbecil científico e esse vídeo é uma vergonha.', 'en' => 'You are a complete scientific imbecile and this video is embarrassing.'],

            // Ciencia positiva
            ['tema' => 'Ciencia', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'A explicação científica ficou clara e ajudou a entender melhor o assunto.', 'en' => 'The scientific explanation was clear and helped me understand the subject better.'],
            ['tema' => 'Ciencia', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'Gostei da forma como você usou dados para sustentar o argumento.', 'en' => 'I liked the way you used data to support the argument.'],
            ['tema' => 'Ciencia', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'O vídeo trouxe uma análise científica equilibrada e bem organizada.', 'en' => 'The video presented a balanced and well-organized scientific analysis.'],
            ['tema' => 'Ciencia', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Excelente explicação, simples sem perder o rigor científico.', 'en' => 'Excellent explanation, simple without losing scientific rigor.'],
            ['tema' => 'Ciencia', 'polaridade' => 'positiva', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Conteúdo brilhante, didático e muito útil para combater desinformação.', 'en' => 'Brilliant, educational, and very useful content for fighting misinformation.'],

            // Futebol negativa
            ['tema' => 'Futebol', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'Não gostei da atuação do time, mas ainda dá para melhorar.', 'en' => 'I did not like the team performance, but there is still room to improve.'],
            ['tema' => 'Futebol', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'O time jogou mal e parecia sem organização em campo.', 'en' => 'The team played badly and seemed disorganized on the field.'],
            ['tema' => 'Futebol', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Esse técnico é fraco e não sabe montar o time direito.', 'en' => 'This coach is weak and does not know how to set up the team properly.'],
            ['tema' => 'Futebol', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Time ridículo, técnico burro e torcida sofrendo com essa porcaria.', 'en' => 'Ridiculous team, stupid coach, and fans suffering with this crap.'],
            ['tema' => 'Futebol', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Esse time é um lixo completo, bando de inúteis sem vergonha.', 'en' => 'This team is complete trash, a bunch of useless shameless people.'],

            // Video negativa
            ['tema' => 'Video', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 1, 'pt' => 'O vídeo poderia ter sido mais objetivo e melhor editado.', 'en' => 'The video could have been more direct and better edited.'],
            ['tema' => 'Video', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 2, 'pt' => 'A explicação ficou confusa e algumas partes parecem mal preparadas.', 'en' => 'The explanation was confusing and some parts seemed poorly prepared.'],
            ['tema' => 'Video', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 3, 'pt' => 'Você falou muita besteira e enrolou o vídeo inteiro.', 'en' => 'You said a lot of nonsense and rambled through the whole video.'],
            ['tema' => 'Video', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 4, 'pt' => 'Vídeo ridículo, explicação burra e perda total de tempo.', 'en' => 'Ridiculous video, stupid explanation, and a complete waste of time.'],
            ['tema' => 'Video', 'polaridade' => 'negativa', 'tipo' => 'texto', 'nivel' => 5, 'pt' => 'Esse vídeo é uma merda completa e você deveria parar de postar lixo.', 'en' => 'This video is complete shit and you should stop posting garbage.'],

            // Emoji negativa
            ['tema' => 'Emoji', 'polaridade' => 'negativa', 'tipo' => 'emoji', 'nivel' => 1, 'pt' => 'Não concordo com você, mas ok 🙂', 'en' => 'I do not agree with you, but okay 🙂'],
            ['tema' => 'Emoji', 'polaridade' => 'negativa', 'tipo' => 'emoji', 'nivel' => 2, 'pt' => 'Você viajou um pouco nessa opinião kkk', 'en' => 'You went a bit off track with that opinion lol'],
            ['tema' => 'Emoji', 'polaridade' => 'negativa', 'tipo' => 'emoji', 'nivel' => 3, 'pt' => 'Você falou besteira demais 😂', 'en' => 'You said way too much nonsense 😂'],
            ['tema' => 'Emoji', 'polaridade' => 'negativa', 'tipo' => 'emoji', 'nivel' => 4, 'pt' => 'Você é burro demais 🤡', 'en' => 'You are so stupid 🤡'],
            ['tema' => 'Emoji', 'polaridade' => 'negativa', 'tipo' => 'emoji', 'nivel' => 5, 'pt' => 'Tu é um imbecil do caralho 🤡😂', 'en' => 'You are a fucking idiot 🤡😂'],

            // Palavrao negativa
            ['tema' => 'Palavrao', 'polaridade' => 'negativa', 'tipo' => 'palavrao', 'nivel' => 1, 'pt' => 'Esse comentário não faz sentido.', 'en' => 'This comment does not make sense.'],
            ['tema' => 'Palavrao', 'polaridade' => 'negativa', 'tipo' => 'palavrao', 'nivel' => 2, 'pt' => 'Esse comentário é muito ruim.', 'en' => 'This comment is very bad.'],
            ['tema' => 'Palavrao', 'polaridade' => 'negativa', 'tipo' => 'palavrao', 'nivel' => 3, 'pt' => 'Esse comentário é uma porcaria.', 'en' => 'This comment is crap.'],
            ['tema' => 'Palavrao', 'polaridade' => 'negativa', 'tipo' => 'palavrao', 'nivel' => 4, 'pt' => 'Esse comentário é uma merda.', 'en' => 'This comment is shit.'],
            ['tema' => 'Palavrao', 'polaridade' => 'negativa', 'tipo' => 'palavrao', 'nivel' => 5, 'pt' => 'Esse comentário é uma merda do caralho.', 'en' => 'This comment is fucking shit.'],
        

        
        ];
    }
}

if (!function_exists('benchmarkGroupKey')) {
    function benchmarkGroupKey(array $row): string
    {
        return implode('|', [
            $row['tema'] ?? 'Geral',
            $row['polaridade'] ?? 'neutra',
            $row['tipo'] ?? 'texto',
        ]);
    }
}

if (!function_exists('benchmarkSummary')) {
    function benchmarkSummary(array $rows, string $field): array
    {
        $byGroup = [];

        foreach ($rows as $row) {
            $byGroup[benchmarkGroupKey($row)][] = $row;
        }

        $ok = 0;
        $total = 0;
        $amplitudes = [];

        foreach ($byGroup as $groupRows) {
            usort($groupRows, fn ($a, $b) => ($a['nivel'] ?? 0) <=> ($b['nivel'] ?? 0));

            $scores = [];

            foreach ($groupRows as $r) {
                if (isset($r[$field]) && is_numeric($r[$field])) {
                    $scores[] = (float) $r[$field];
                }
            }

            if (count($scores) >= 2) {
                $amplitudes[] = max($scores) - min($scores);
            }

            for ($i = 1; $i < count($groupRows); $i++) {
                $a = $groupRows[$i - 1][$field] ?? null;
                $b = $groupRows[$i][$field] ?? null;

                if (is_numeric($a) && is_numeric($b)) {
                    $total++;

                    if ((float) $b >= (float) $a) {
                        $ok++;
                    }
                }
            }
        }

        return [
            'ordem_preservada_pct' => $total > 0 ? round(($ok / $total) * 100, 1) : null,
            'amplitude_media' => count($amplitudes) ? round(array_sum($amplitudes) / count($amplitudes), 3) : null,
            'grupos_validos' => count($amplitudes),
            'pares_validos' => $total,
        ];
    }
}

Route::get('/benchmarks', function () {
    $lang = request('lang', 'pt');
    $lang = in_array($lang, ['pt', 'en'], true) ? $lang : 'pt';

    $rows = benchmarkDataset();

    foreach ($rows as &$row) {
        $txt = $row[$lang] ?? $row['pt'] ?? '';


        $detox = benchmarkDetoxify($txt, 'multilingual');

        $row['detox_toxicity'] = $detox['toxicity'] ?? null;
        $row['detox_severe_toxicity'] = $detox['severe_toxicity'] ?? null;
        $row['detox_insult'] = $detox['insult'] ?? null;
        $row['detox_threat'] = $detox['threat'] ?? null;
        $row['detox_identity_attack'] = $detox['identity_attack'] ?? null;
        $row['detox_obscene'] = $detox['obscene'] ?? null;


        $row['lang'] = $lang;
        $row['texto'] = $txt;
        $row['grupo'] = benchmarkGroupKey($row);

        $row['perspective'] = benchmarkPerspective($txt);

        $row['llm'] = benchmarkGpt($txt);

        $google = benchmarkGoogleNLP($txt);
        $row['gss']  = $google['gss'];
        $row['gsm']  = $google['gsm'];
        $row['gtp']  = $google['gtp'];
        $row['gtpp'] = $google['gtpp'];


        $google = benchmarkGoogleNLP($txt);

        $row['gss'] = $google['gss'];
        $row['gsm'] = $google['gsm'];
        $row['gtp'] = $google['gtp'];
        $row['gtpp'] = $google['gtpp'];


        // GotIt foi mantido como coluna opcional, mas não entra no resumo.
        $row['gotit'] = null;
    }
    unset($row);


    // $summary = [
    // 'Perspective' => benchmarkSummary($rows, 'perspective'),
    // 'GPT' => benchmarkSummary($rows, 'gpt'),
    // 'Google NLP sem amp.' => benchmarkSummary($rows, 'google_raw'),
    // 'Google NLP com amp.' => benchmarkSummary($rows, 'google_amp'),
    // 'Detoxify' => benchmarkSummary($rows, 'detox_toxicity'), // 👈 FALTOU ESSA LINHA
    // ];


    $summary = [
        'Perspective' => benchmarkSummary($rows, 'perspective'),
        'LLM' => benchmarkSummary($rows, 'llm'),
        'Detoxify' => benchmarkSummary($rows, 'detox_toxicity'),
        'GTP' => benchmarkSummary($rows, 'gtp'),
        'GTPP' => benchmarkSummary($rows, 'gtpp'),
    ];    


    $html = '<!doctype html><html><head><meta charset="utf-8"><title>Benchmarks</title>
    <style>
        body{font-family:Arial,sans-serif;padding:20px;color:#111}
        table{border-collapse:collapse;width:100%;margin:20px 0}
        th,td{border:1px solid #ccc;padding:6px 8px;font-size:12px;vertical-align:top}
        th{background:#f5f5f5}
        .num{text-align:right;white-space:nowrap}
        .small{font-size:12px;color:#555}
        .pill{display:inline-block;padding:2px 6px;border:1px solid #ccc;border-radius:10px;background:#fafafa}
        h1,h2{margin:0 0 10px}
        a{margin-right:10px}

        .google{background:#d9ead3}

    </style></head><body>';

    $html .= '<h1>Benchmark de Sensibilidade de Toxicidade</h1>';
    $html .= '<p>Idioma: <strong>' . e(strtoupper($lang)) . '</strong></p>';
    $html .= '<p><a href="/benchmarks?lang=pt">Português</a> <a href="/benchmarks?lang=en">English</a></p>';
    $html .= '<p class="small">Teste exploratório com blocos temáticos de 5 níveis progressivos. Cada grupo é definido por tema + polaridade + tipo.</p>';




    $html .= '<h2>Resumo</h2><table><tr>
        <th>Ferramenta</th>
        <th>Ordem preservada (%)</th>
        <th>Amplitude média</th>
        <th>Grupos válidos</th>
        <th>Pares válidos</th>
    </tr>';

    foreach ($summary as $tool => $stats) {
        $html .= '<tr>
            <td>' . e($tool) . '</td>
            <td class="num">' . e((string) ($stats['ordem_preservada_pct'] ?? '')) . '</td>
            <td class="num">' . e((string) ($stats['amplitude_media'] ?? '')) . '</td>
            <td class="num">' . e((string) ($stats['grupos_validos'] ?? '')) . '</td>
            <td class="num">' . e((string) ($stats['pares_validos'] ?? '')) . '</td>
        </tr>';
    }

    $html .= '</table>';

    usort($rows, function ($a, $b) {
        return [$a['tema'], $a['polaridade'], $a['tipo'], $a['nivel']]
            <=> [$b['tema'], $b['polaridade'], $b['tipo'], $b['nivel']];
    });


    $html .= '<p class="small">
    <strong>Siglas:</strong>
    Persp = Perspective API;
    LLM = modelo de linguagem via prompt;
    Detoxify = modelo open-source;
    GSS = Google Sentiment Score;
    GSM = Google Sentiment Magnitude;
    GTP = Google Toxicity Proxy;
    GTPP = Google Toxicity Proxy Ponderada.
    </p>';

    $html .= '<h2>Resultados detalhados</h2><table><tr>
    <th>Tema</th>
    <th>Polaridade</th>
    <th>Tipo</th>
    <th>Nível</th>
    <th>Texto</th>
    <th>Persp</th>
    <th>LLM</th>
    <th>Detoxify</th>
    <th>GSS</th>
    <th>GSM</th>
    <th>GTP</th>
    <th>GTPP</th>
</tr>';

    foreach ($rows as $r) {
        $html .= '<tr>
            <td>' . e($r['tema'] ?? '') . '</td>
            <td><span class="pill">' . e($r['polaridade'] ?? '') . '</span></td>
            <td>' . e($r['tipo'] ?? '') . '</td>
            <td class="num">' . e((string) ($r['nivel'] ?? '')) . '</td>
            <td>' . e($r['texto'] ?? '') . '</td>
            <td class="num">' . e(number_format((float) ($r['perspective'] ?? 0), 3, '.', '')) . '</td>
            <td class="num">' . e(number_format((float) ($r['llm'] ?? 0), 3, '.', '')) . '</td>
            <td class="num">' . e(number_format((float) ($r['detox_toxicity'] ?? 0), 5, '.', '')) . '</td>
            <td class="num google">' . e(number_format((float) ($r['gss'] ?? 0), 3, '.', '')) . '</td>
            <td class="num google">' . e(number_format((float) ($r['gsm'] ?? 0), 3, '.', '')) . '</td>
            <td class="num google">' . e(number_format((float) ($r['gtp'] ?? 0), 3, '.', '')) . '</td>
            <td class="num google">' . e(number_format((float) ($r['gtpp'] ?? 0), 3, '.', '')) . '</td>
        </tr>';

    }


    $html .= '</table></body></html>';

    return response($html);
});




#######################################################################################
#######################################################################################


#Auth::loginUsingId(7);

Route::get('/', function () {
    return view('home');
})->name('home');



    Route::get('tarefa1', Tarefa1::class)->name('tarefa1');
    Route::get('tarefa2', Tarefa2::class)->name('tarefa2');
    Route::get('tarefa3', Tarefa3::class)->name('tarefa3');
    Route::get('tarefa4', Tarefa4::class)->name('tarefa4');
    Route::get('resultados', Resultados::class)->name('resultados');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


Route::get('polarizacao', Polarizacao::class)->name('polarizacao');
Route::get('toxicidade', Toxicidade::class)->name('toxicidade');
Route::get('monetizacao', Monetizacao::class)->name('monetizacao');
Route::get('tese', Tese::class)->name('tese');



Route::get('busca', Busca::class)->name('busca');
Route::get('video', Video::class)->name('video');
Route::get('canal', Canal::class)->name('canal');
Route::get('monet', Monet::class)->name('monet');


Route::get('arxiv/{canal_id?}', Arxiv::class)->name('arxiv');

Route::get('graf/{canal?}', Graf::class)->name('graf');
Route::get('toxic/{video?}', Toxic::class)->name('toxic');
Route::get('nlp/{busca?}', Nlp::class)->name('nlp');

Route::get('comentario/{video_id?}', Comentario::class)->name('comentario');



require __DIR__ . '/auth.php';
