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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;


################################# INICIO INDUCAO PMT ####################################
#######################################################################################

$url = "https://www.youtube.com/watch?v=LDG8GFqVnKQ";




Route::get('/bench/pmt', function () {

    $videoUrl = request('url');



    if (!$videoUrl) {
        return response()->json([
            'erro' => 'Informe ?url=https://www.youtube.com/watch?v=VIDEO_ID'
        ]);
    }


    $pmt = new class {
        public function run(string $videoUrl): array
        {

$bench = [];
$totalStart = microtime(true);

$mark = function (string $label, callable $fn) use (&$bench) {
    $ini = microtime(true);
    $result = $fn();
    $bench[$label] = round(microtime(true) - $ini, 3);
    return $result;
};



            $videoId = $this->extractVideoId($videoUrl);
            if (!$videoId) {
                return ['erro' => 'videoId não identificado'];
            }
            #$video = $this->getVideoDetails($videoId);
$video = $mark('YouTube API - vídeo', function () use ($videoId) {
    return $this->getVideoDetails($videoId);
});

          
        if (!$video || !isset($video['channelId'])) {
            return [
                'erro' => 'Vídeo não encontrado ou channelId ausente',
                'videoId' => $videoId,
                'video' => $video,
            ];
        }

            $channelId = $video['channelId'];           
           
            #$channel = $this->getChannelDetails($channelId);

$channel = $mark('YouTube API - canal', function () use ($channelId) {
    return $this->getChannelDetails($channelId);
});

            #$comments = $this->getTopComments($videoId, 100);

$comments = $mark('YouTube API - comentários', function () use ($videoId) {
    return $this->getTopComments($videoId, 100);
});



$persistencia = $mark('Banco de dados - persistência', function () use ($video, $channel, $comments, $videoId, $channelId) {

            // CANAL
            $canalDB = \App\Models\Canal::updateOrCreate(
                ['youtube_id' => $channelId],
                [
                    'nome' => $channel['channelTitle'] ?? $video['channelTitle'] ?? null,
                    'desc' => $channel['channelDesc'] ?? null,
                    'inscritos' => $channel['subscriberCount'] ?? null,
                    'views' => $channel['viewCount'] ?? null,
                    'videos' => $channel['videoCount'] ?? null,
                    'dt' => $channel['published'] ?? null,
                ]
            );


            // VIDEO
            $videoDB = \App\Models\Video::updateOrCreate(
                [
                    'cod' => $videoId,
                    'canal_id' => $canalDB->id,
                    'tarefa_id' => 1,
                ],
                [
                    'nome' => $video['videoTitle'] ?? null,
                    'desc' => $video['videoDesc'] ?? null,
                    'comments' => $video['commentCount'] ?? null,
                    'likes' => $video['likeCount'] ?? null,
                    'views' => $video['viewCount'] ?? null,
                    'duration' => $video['duration'] ?? null,
                    'categ_id' => $video['videoCategId'] ?? null,
                    'dt' => $video['published'] ?? null,
                ]
            );


            // COMENTÁRIOS
            foreach ($comments as $c) {
                \App\Models\Comentario::updateOrCreate(
                    [
                        'cod' => $c['cod'],
                        'video_id' => $videoDB->id,
                        'tarefa_id' => 1,
                    ],
                    [
                        'username' => $c['username'] ?? null,
                        'texto' => $c['texto'] ?? null,
                        'likes' => $c['likes'] ?? null,
                        'dt' => $c['dt'] ?? now(),
                    ]
                );
            }


    return true;
});

           

            #$bucketVideos = $this->getBucketVideos($channel);
$bucketVideos = $mark('YouTube API - vídeos do canal', function () use ($channel) {
    return $this->getBucketVideos($channel);
});


$toxResult = $mark('Perspective API - toxicidade', function () use ($comments) {
    return $this->analyzeCommentsToxicity($comments);
});
            #$toxResult = $this->analyzeCommentsToxicity($comments);
            $toxicity = $toxResult['summary'];
            $comments = $toxResult['comments'];


            #$transcript = $this->getTranscript($videoId);

$transcript = $mark('SearchAPI - transcrição', function () use ($videoId) {
    return $this->getTranscript($videoId);
});
            $transcriptWords = $transcript ? str_word_count(strip_tags($transcript)) : 0;

          
            #$vidiq = $this->getVidIqMonthlyAvgUsd($channelId);
$vidiq = $mark('VidIQ - monetização estimada', function () use ($channelId) {
    return $this->getVidIqMonthlyAvgUsd($channelId);
});

            #$externalUrls = $this->extractExternalUrls(
            #    ($video['videoDesc'] ?? '') . "\n" . ($channel['channelDesc'] ?? '')
            #);

$urls = $mark('Extração de URLs externas', function () use ($video, $channel) {
    $videoUrls = $this->extractExternalUrls($video['videoDesc'] ?? '');
    $channelUrls = $this->extractExternalUrls($channel['channelDesc'] ?? '');

    return [
        'video_urls' => $videoUrls,
        'channel_urls' => $channelUrls,
        'external_urls' => array_values(array_unique(array_merge($videoUrls, $channelUrls))),
    ];
});



$llm = $mark('OpenAI LLM - polarização', function () use ($video, $channel, $transcript, $comments, $toxicity, $vidiq, $urls) {
    return $this->classifyPolarization([
        'video' => $video,
        'channel' => $channel,
        'transcript' => $transcript,
        'comments' => array_slice($comments, 0, 20),
        'toxicity' => $toxicity,
        'vidiq_monthly_avg_usd' => $vidiq,
        'external_urls_count' => count($urls['external_urls']),
        'external_urls' => $urls['external_urls'],
    ]);
});            

$bench['TOTAL'] = round(microtime(true) - $totalStart, 3);

            return [

'bench' => $bench,
                
                'video' => $video,
                'channel' => $channel,
                'bucket_videos_count' => count($bucketVideos),
                'bucket_videos_sample' => array_slice($bucketVideos, 0, 10),
                'comments_count' => count($comments),
                'transcript_chars' => mb_strlen($transcript ?? ''),
                'transcript_words' => $transcriptWords,
                'comments_sample' => array_slice($comments, 0, 10),
                'toxicity' => $toxicity,
                // 'monetization' => [
                //     'vidiq_monthly_avg_usd' => $vidiq,
                //     'external_urls_count' => count($externalUrls),
                //     'external_urls' => $externalUrls,
                // ],

                'monetization' => [
                    'vidiq_monthly_avg_usd' => $vidiq,
                    'video_urls_count' => count($urls['video_urls']),
                    'channel_urls_count' => count($urls['channel_urls']),
                    'external_urls_count' => count($urls['external_urls']),
                    'external_urls' => $urls['external_urls'],
                ],


                'transcript_chars' => mb_strlen($transcript ?? ''),
                'llm' => $llm,
            ];




        }

        protected function extractVideoId(string $url): ?string
        {
            if (preg_match('~v=([^&]+)~', $url, $m)) return $m[1];
            if (preg_match('~youtu\.be/([^?&/]+)~', $url, $m)) return $m[1];
            if (preg_match('~shorts/([^?&/]+)~', $url, $m)) return $m[1];

            return null;
        }


        protected function getVideoDetails(string $videoId): ?array
        {
            $res = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/videos', [
                'key' => env('YOUTUBE_API_KEY'),
                'id' => $videoId,
                'part' => 'snippet,statistics,contentDetails',
            ]);

            $item = $res->json('items.0');



            if (!$item) return null;

            $sn = $item['snippet'] ?? [];
            $st = $item['statistics'] ?? [];
            $cd = $item['contentDetails'] ?? [];

            return [
                'videoId' => $videoId,
                'channelId' => $sn['channelId'] ?? null,
                'channelTitle' => $sn['channelTitle'] ?? null,
                'published' => $sn['publishedAt'] ?? null,
                'videoTitle' => $sn['title'] ?? '',
                'videoDesc' => $sn['description'] ?? '',
                'videoTags' => $sn['tags'] ?? [],
                'videoCategId' => $sn['categoryId'] ?? null,
                'lang' => $sn['defaultAudioLanguage'] ?? ($sn['defaultLanguage'] ?? null),
                'viewCount' => (int) ($st['viewCount'] ?? 0),
                'likeCount' => (int) ($st['likeCount'] ?? 0),
                'commentCount' => (int) ($st['commentCount'] ?? 0),
                'duration' => $this->isoDurationToSeconds($cd['duration'] ?? null),
            ];
        }

        protected function getChannelDetails(string $channelId): array
        {
            $res = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/channels', [
                'key' => env('YOUTUBE_API_KEY'),
                'id' => $channelId,
                'part' => 'snippet,statistics,brandingSettings',
            ]);

            $ch = $res->json('items.0') ?? [];

            $sn = $ch['snippet'] ?? [];
            $st = $ch['statistics'] ?? [];
            $br = $ch['brandingSettings']['channel'] ?? [];

            return [
                'channelId' => $channelId,
                'channelTitle' => $sn['title'] ?? null,
                'channelDesc' => $sn['description'] ?? null,
                'channelDt' => $sn['publishedAt'] ?? null,
                'channelCountry' => $sn['country'] ?? null,
                'channelSubs' => (int) ($st['subscriberCount'] ?? 0),
                'channelViews' => (int) ($st['viewCount'] ?? 0),
                'channelVideos' => (int) ($st['videoCount'] ?? 0),
                'channelKeywords' => $this->parseKeywords($br['keywords'] ?? ''),
            ];
        }

        protected function getBucketVideos(array $channel): array
        {
            $created = $channel['channelDt'] ?? null;

            if (!$created) return [];

            $start = \Carbon\Carbon::parse($created);
            $end = now();
            $days = max(1, $start->diffInDays($end));
            $step = max(1, intdiv($days, 5));

            $allIds = [];

            for ($i = 0; $i < 5; $i++) {
                $after = $start->copy()->addDays($i * $step)->toIso8601String();
                $before = $i === 4
                    ? $end->toIso8601String()
                    : $start->copy()->addDays(($i + 1) * $step)->toIso8601String();

                $ids = $this->searchVideos($channel['channelId'], $after, $before, 50);

                $allIds = array_merge($allIds, $ids);
            }

            $allIds = array_values(array_unique($allIds));

            return $this->getVideoDetailsBatch($allIds);
        }

        protected function searchVideos(string $channelId, string $after, string $before, int $max = 50): array
        {
            $res = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/search', [
                'key' => env('YOUTUBE_API_KEY'),
                'channelId' => $channelId,
                'part' => 'snippet',
                'type' => 'video',
                'order' => 'date',
                'publishedAfter' => $after,
                'publishedBefore' => $before,
                'maxResults' => $max,
            ]);

            return collect($res->json('items') ?? [])
                ->pluck('id.videoId')
                ->filter()
                ->values()
                ->all();
        }

        protected function getVideoDetailsBatch(array $ids): array
        {
            $out = [];

            foreach (array_chunk($ids, 50) as $chunk) {
                $res = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/videos', [
                    'key' => env('YOUTUBE_API_KEY'),
                    'id' => implode(',', $chunk),
                    'part' => 'snippet,statistics,contentDetails',
                ]);

                foreach ($res->json('items') ?? [] as $item) {
                    $sn = $item['snippet'] ?? [];
                    $st = $item['statistics'] ?? [];

                    $out[] = [
                        'videoId' => $item['id'] ?? null,
                        'title' => $sn['title'] ?? '',
                        'published' => $sn['publishedAt'] ?? null,
                        'views' => (int) ($st['viewCount'] ?? 0),
                        'likes' => (int) ($st['likeCount'] ?? 0),
                        'comments' => (int) ($st['commentCount'] ?? 0),
                    ];
                }
            }

            return $out;
        }

        protected function getTopComments(string $videoId, int $max = 100): array
        {
            $res = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/commentThreads', [
                'key' => env('YOUTUBE_API_KEY'),
                'videoId' => $videoId,
                'part' => 'snippet',
                'maxResults' => min($max, 100),
                'order' => 'relevance',
                'textFormat' => 'plainText',
            ]);

            return collect($res->json('items') ?? [])
                ->map(function ($item) {
                    $sn = $item['snippet']['topLevelComment']['snippet'] ?? [];

                    return [
                        // id único do comentário no YouTube
                        'cod' => $item['snippet']['topLevelComment']['id']
                            ?? $item['id']
                            ?? null,

                        // nomes compatíveis com sua tabela comentarios
                        'username' => $sn['authorDisplayName'] ?? null,
                        'texto' => $sn['textDisplay'] ?? '',
                        'likes' => (int) ($sn['likeCount'] ?? 0),
                        'dt' => $sn['publishedAt'] ?? null,

                        // opcionais, se quiser manter compatibilidade com o restante do bench
                        'author' => $sn['authorDisplayName'] ?? null,
                        'text' => $sn['textDisplay'] ?? '',
                        'likeCount' => (int) ($sn['likeCount'] ?? 0),
                        'publishedAt' => $sn['publishedAt'] ?? null,
                    ];
                })
                ->filter(fn ($c) => !empty($c['cod']) && trim($c['texto']) !== '')
                ->values()
                ->all();
        }

        protected function getTranscript(string $videoId): ?string
        {
            try {
                $res = Http::connectTimeout(20)
                    ->timeout(240)
                    ->retry(2, 5000)
                    ->get('https://www.searchapi.io/api/v1/search', [
                        'engine' => 'youtube_transcripts',
                        'video_id' => $videoId,
                        'api_key' => env('SEARCHAPI_TRANSCRIPTS_YOUTUBE_API'),
                        'only_available' => 'true',
                        'transcript_type' => 'auto',
                    ]);

                if (!$res->successful()) return null;

                $texts = [];

                foreach ($res->json('transcripts') ?? [] as $item) {
                    $text = trim($item['text'] ?? '');
                    if ($text === '') continue;
                    if (preg_match('/^\[(music|applause|laughter)\]$/i', $text)) continue;
                    $texts[] = $text;
                }

                return mb_substr(preg_replace('/\s+/', ' ', implode(' ', $texts)), 0, 30000);
            } catch (\Throwable $e) {
                Log::warning('Erro transcript PMT', ['videoId' => $videoId, 'erro' => $e->getMessage()]);
                return null;
            }
        }

        protected function analyzeCommentsToxicity(array $comments): array
        {
            $scores = [];

            foreach ($comments as &$comment) {
                $texto = $comment['texto'] ?? $comment['text'] ?? '';

                $score = $this->perspectiveToxicity($texto);

                $comment['tox'] = $score;

                if ($score !== null) {
                    $scores[] = $score;
                }

                usleep(150000);
            }

            return [
                'summary' => [
                    'n' => count($scores),
                    'avg_toxicity' => $scores ? round(array_sum($scores) / count($scores), 4) : null,
                    'max_toxicity' => $scores ? round(max($scores), 4) : null,
                    'high_toxicity_rate' => $scores
                        ? round(count(array_filter($scores, fn ($x) => $x >= 0.7)) / count($scores), 4)
                        : null,
                ],
                'comments' => $comments,
            ];
        }

        protected function perspectiveToxicity(string $text): ?float
        {
            try {
                if (trim($text) === '') {
                    return null;
                }

                $res = Http::timeout(30)->post(
                    'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . env('PERSPECTIVE_API'),
                    [
                        'comment' => ['text' => mb_substr($text, 0, 3000)],
                        'languages' => ['pt'],
                        'requestedAttributes' => [
                            'TOXICITY' => new \stdClass(),
                        ],
                    ]
                );

                if (!$res->successful()) {
                    \Log::warning('Perspective API erro', [
                        'status' => $res->status(),
                        'body' => $res->body(),
                    ]);
                    return null;
                }

                return $res->json('attributeScores.TOXICITY.summaryScore.value');

            } catch (\Throwable $e) {
                \Log::warning('Perspective exception', [
                    'erro' => $e->getMessage(),
                ]);
                return null;
            }
        }


        protected function classifyPolarization(array $payload): array
        {
            $video = $payload['video'];
            $channel = $payload['channel'];

            $tags = implode(', ', $video['videoTags'] ?? []);

            $prompt = <<<PROMPT
            Você é um classificador acadêmico para análise de polarização, monetização e toxicidade em vídeos do YouTube.
            Analise o vídeo com base apenas nos dados fornecidos.
            Dados do vídeo:
            Título: {$video['videoTitle']}
            Descrição: {$video['videoDesc']}
            Tags: {$tags}
            Idioma: {$video['lang']}
            Canal: {$channel['channelTitle']}
            Descrição do canal: {$channel['channelDesc']}

            Transcrição:
            {$payload['transcript']}

            Comentários analisados:
            Toxicidade média: {$payload['toxicity']['avg_toxicity']}
            Toxicidade máxima: {$payload['toxicity']['max_toxicity']}
            Taxa alta toxicidade: {$payload['toxicity']['high_toxicity_rate']}

            Monetização:
            VidIQ média mensal USD: {$payload['vidiq_monthly_avg_usd']}
            URLs externas: {$payload['external_urls_count']}

            Classifique:
            - polarizacao_score: escala 1 a 5, sendo 1 = muito negativo/hostil/polarizado, 2 = polarizado, 3 = neutro/ambíguo, 4 = positivo/moderado, 5 = muito positivo/construtivo.
            - polarizacao_categoria: politica, ciencia, religiao, genero, conspiracao, outro ou indefinido.
            - polo_ideologico: esquerda, direita, centro, cientifico, anti-ciencia, conspiratorio, cristao, afro-brasileiro, islamico, ateu-secular, outro ou indefinido.
            - confidence: 0 a 1.
            - is_ambiguous: true/false.
            - evidences: 2 evidências curtas, indicando se vieram de título, descrição, transcrição ou comentários.

            Responda exclusivamente em JSON válido.
            PROMPT;

            $res = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini',
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Você é um classificador acadêmico. Responda apenas em JSON válido.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (!$res->successful()) {
                return [
                    'erro' => true,
                    'body' => $res->body(),
                ];
            }

            return json_decode($res->json('choices.0.message.content'), true) ?: [];
        }

        protected function extractExternalUrls(string $text): array
        {
            preg_match_all('~https?://[^\s\)\]\}<>"]+~i', $text, $m);

            return collect($m[0] ?? [])
                ->map(fn ($url) => rtrim($url, '.,;:'))
                ->reject(fn ($url) => Str::contains($url, [
                    'youtube.com',
                    'youtu.be',
                    'google.com',
                    'gstatic.com',
                ]))
                ->unique()
                ->values()
                ->all();
        }

        private function getVidIqMonthlyAvgUsd(string $channelId): ?float
        {
            $url = "https://vidiq.com/youtube-stats/channel/{$channelId}/";

            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept-Language' => 'pt-BR,pt;q=0.9,en-US,en;q=0.8',
                ])->timeout(30)->get($url);

                if (!$res->ok()) {
                    return null;
                }

                $html = $res->body();

                // PT-BR: Ganhos mensais estimados ... $19K
                if (preg_match(
                    '~Ganhos\s+mensais\s+estimados.*?<p[^>]*>\s*([^<]+)\s*</p>~is',
                    $html,
                    $m
                )) {
                    return $this->parseRangeToAvgUsd(trim($m[1]));
                }

                // EN fallback
                if (preg_match(
                    '~Est\.\s*Monthly\s*Earnings.*?<p[^>]*>\s*([^<]+)\s*</p>~is',
                    $html,
                    $m
                )) {
                    return $this->parseRangeToAvgUsd(trim($m[1]));
                }

                // fallback mais bruto: procura valor próximo de "earnings" ou "ganhos"
                if (preg_match(
                    '~(?:Ganhos\s+mensais\s+estimados|Monthly\s*Earnings).*?(\$[0-9][0-9\.,]*\s*[KkMm]?)~is',
                    $html,
                    $m
                )) {
                    return $this->parseRangeToAvgUsd(trim($m[1]));
                }

                return null;

            } catch (\Throwable $e) {
                return null;
            }
        }

        private function parseRangeToAvgUsd(string $rangeText): ?float
        {
            $rangeText = html_entity_decode($rangeText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $rangeText = trim(strip_tags($rangeText));

            if (preg_match('~\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?\s*-\s*\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?~', $rangeText, $m)) {
                $min = $this->toNumber($m[1], $m[2] ?? null);
                $max = $this->toNumber($m[3], $m[4] ?? null);

                return ($min + $max) / 2;
            }

            if (preg_match('~\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?~', $rangeText, $m)) {
                return $this->toNumber($m[1], $m[2] ?? null);
            }

            return null;
        }

        private function toNumber(string $num, ?string $suffix): ?float
        {
            $num = str_replace(',', '', $num);
            $val = (float) $num;

            return match (strtolower($suffix ?? '')) {
                'k' => $val * 1000,
                'm' => $val * 1000000,
                default => $val,
            };
        }

        protected function parseKeywords(string $text): array
        {
            preg_match_all('/"([^"]+)"|(\S+)/', $text, $m);

            return collect($m[1])
                ->merge($m[2])
                ->filter()
                ->values()
                ->all();
        }

        protected function isoDurationToSeconds(?string $duration): int
        {
            if (!$duration) return 0;

            try {
                $interval = new \DateInterval($duration);
                return ($interval->h * 3600) + ($interval->i * 60) + $interval->s + ($interval->d * 86400);
            } catch (\Throwable $e) {
                return 0;
            }
        }
    };


    

$result = $pmt->run($videoUrl);

if (isset($result['erro'])) {
    return response()->json($result);
}

$comments = $result['comments_sample'] ?? [];
$commentsPreview = collect($comments)->take(5);
$totalComentarios = $result['comments_count'] ?? count($comments);

#$tempo = round(microtime(true) - $start, 3);
$tempo = $result['bench']['TOTAL'] ?? 0;

$video = $result['video'];
$channel = $result['channel'];
$toxicity = $result['toxicity'] ?? [];


$monetization = $result['monetization'] ?? [];
$llm = $result['llm'] ?? [];


$html = "<h2>DashTube - Bench PMT</h2>";
$html .= "<h1><strong>Tempo de processamento:</strong> {$tempo}s</h1>";

$html .= "<h3>Vídeo</h3>";
$html .= "<table border='1' cellpadding='6' cellspacing='0'>
<tr>
    <th>ID YouTube</th>
    <th>Título</th>
    <th>Publicado em</th>
    <th>Views</th>
    <th>Likes</th>
    <th>Comentários</th>
</tr>
<tr>
    <td>" . e($video['videoId'] ?? '') . "</td>
    <td>" . e($video['videoTitle'] ?? '') . "</td>
    <td>" . e($video['published'] ?? '') . "</td>
    <td>" . e($video['viewCount'] ?? '') . "</td>
    <td>" . e($video['likeCount'] ?? '') . "</td>
    <td>" . e($video['commentCount'] ?? '') . "</td>
</tr>
</table>";

$html .= "<h3>Canal</h3>";
$html .= "<table border='1' cellpadding='6' cellspacing='0'>
<tr>
    <th>ID YouTube</th>
    <th>Nome</th>
    <th>Criado em</th>
    <th>Inscritos</th>
    <th>Views</th>
    <th>Vídeos</th>
</tr>
<tr>
    <td>" . e($channel['channelId'] ?? '') . "</td>
    <td>" . e($channel['channelTitle'] ?? '') . "</td>
    <td>" . e($channel['channelDt'] ?? '') . "</td>
    <td>" . e($channel['channelSubs'] ?? '') . "</td>
    <td>" . e($channel['channelViews'] ?? '') . "</td>
    <td>" . e($channel['channelVideos'] ?? '') . "</td>
</tr>
</table>";

$html .= "<h3>Comentários salvos / analisados (amostra)</h3>";
$html .= "<table border='1' cellpadding='6' cellspacing='0'>
<tr>
    <th>ID comentário</th>
    <th>Usuário</th>
    <th>Data</th>
    <th>Texto</th>
    <th>Likes</th>
    <th>Toxicidade</th>
</tr>";

foreach ($commentsPreview as $c) {
    $texto = mb_substr($c['texto'] ?? $c['text'] ?? '', 0, 100);

    $html .= "<tr>
        <td>" . e($c['cod'] ?? '') . "</td>
        <td>" . e($c['username'] ?? $c['author'] ?? '') . "</td>
        <td>" . e($c['dt'] ?? $c['publishedAt'] ?? '') . "</td>
        <td>" . e($texto) . "...</td>
        <td>" . e($c['likes'] ?? $c['likeCount'] ?? 0) . "</td>

        <td>" . e(isset($c['tox']) ? round($c['tox'], 4) : '-') . "</td>
    </tr>";
}

if ($totalComentarios > 5) {
    $html .= "<tr><td colspan='6' align='center'>...</td></tr>";
}

$html .= "</table>";

$urls = $monetization['external_urls'] ?? [];
$urlsHtml = empty($urls)
    ? '-'
    : implode('<br>', array_map(fn($u) => e($u), array_slice($urls, 0, 5)));

$html .= "<h3>Análise PMT em tempo de execução</h3>";
$html .= "<table border='1' cellpadding='6' cellspacing='0'>
<tr>
<th>Palavras transcript</th>
    <th>Toxicidade média</th>
    <th>Toxicidade máxima</th>
    <th>Taxa alta tox.</th>
    <th>Categoria polarização</th>
    <th>Score polarização</th>
    <th>Polo ideológico</th>
    <th>Confiança</th>
    <th>URLs monetização</th>
    <th>VidIQ US$/mês</th>
</tr>
<tr>
<td>" . e($result['transcript_words'] ?? 0) . "</td>
    <td>" . e($toxicity['avg_toxicity'] ?? '-') . "</td>
    <td>" . e($toxicity['max_toxicity'] ?? '-') . "</td>
    <td>" . e($toxicity['high_toxicity_rate'] ?? '-') . "</td>
    <td>" . e($llm['polarizacao_categoria'] ?? '-') . "</td>
    <td>" . e($llm['polarizacao_score'] ?? '-') . "</td>
    <td>" . e($llm['polo_ideologico'] ?? '-') . "</td>
    <td>" . e($llm['confidence'] ?? '-') . "</td>
    <td>" . e($monetization['external_urls_count'] ?? 0) . "</td>
    <td>US$ " . e($monetization['vidiq_monthly_avg_usd'] ?? '-') . "</td>
</tr>
</table>";

$html .= "<h4>URLs externas detectadas</h4>";
$html .= "<p>{$urlsHtml}</p>";





$bench = $result['bench'] ?? [];
$totalBench = $bench['TOTAL'] ?? array_sum($bench);

$html .= "<h3>Tempo de processamento por etapa</h3>";
$html .= "<table border='1' cellpadding='6' cellspacing='0'>
<tr>
    <th>Etapa</th>
    <th>Tempo (s)</th>
    <th>% do total</th>
</tr>";

foreach ($bench as $etapa => $segundos) {
    if ($etapa === 'TOTAL') {
        continue;
    }

    $pct = $totalBench > 0 ? round(($segundos / $totalBench) * 100, 2) : 0;

    $html .= "<tr>
        <td>" . e($etapa) . "</td>
        <td>" . e($segundos) . "</td>
        <td>" . e($pct) . "%</td>
    </tr>";
}

$html .= "<tr>
    <th>Total</th>
    <th>" . e($totalBench) . "</th>
    <th>100%</th>
</tr>";

$html .= "</table>";



return $html;








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
