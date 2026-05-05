<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RunPolarizationBenchmark extends Command
{

protected string $transcriptsDir = 'polarization/transcripts';

protected string $promptsDir = 'bench/prompts';


protected $signature = 'bench:polarization
    {--mode=all : collect|llm|csv|all}
    {--limit=50 : vídeos por bucket}
    {--buckets=5 : número de buckets temporais}
    {--max-llm=0 : máximo de vídeos para processar no LLM; 0 = todos}
    {--labs=base,video : blocos do prompt: base,video,stats,channel,transcript}
    {--fresh=0 : se 1, limpa results/jsonl e csv antes de rodar}
    {--only-transcripts=0 : se 1, processa apenas vídeos com transcrição salva}';    


// base       = título + idioma
// video      = descrição + tags + categoria
// stats      = views + likes + comentários + duração
// channel    = nome/descrição/keywords do canal
// transcript = transcrição


    protected $description = 'Coleta vídeos e classifica polarização via LLM';

    protected string $videosFile = 'bench/polarization_videos.json';
    protected string $resultsFile = 'bench/polarization_results.jsonl';
    protected string $csvFile = 'bench/polarization_results.csv';


protected function getLabs2222222222222(): array
{
    $labs = (string) $this->option('labs');

    return collect(explode(',', $labs))
        ->map(fn ($x) => trim($x))
        ->filter()
        ->unique()
        ->values()
        ->all();
}

protected function getLabs(): array
{
    $labs = (string) $this->option('labs');

    $out = collect(explode(',', $labs))
        ->map(fn ($x) => trim($x))
        ->filter()
        ->unique()
        ->values()
        ->all();

    if (!in_array('base', $out, true)) {
        array_unshift($out, 'base');
    }

    $allowed = ['base', 'video', 'stats', 'channel', 'transcript'];

    return array_values(array_intersect($out, $allowed));
}

protected function labsKey(array $labs): string
{
    sort($labs);
    return implode('+', $labs);
}

protected function hasTranscript(string $videoId): bool
{
    return Storage::exists($this->transcriptsDir . '/' . $videoId . '.json');
}

    public function handle(): int
    {
        Storage::makeDirectory('bench');

        $mode = $this->option('mode');

        if ((int) $this->option('fresh') === 1) {
            Storage::delete($this->resultsFile);
            Storage::delete($this->csvFile);
            $this->warn('Resultados anteriores apagados.');
        }


        if (in_array($mode, ['collect', 'all'])) {
            $videos = $this->collectAllVideos();
            Storage::put($this->videosFile, json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Vídeos coletados e salvos: ' . count($videos));
        }

        if (in_array($mode, ['llm', 'all'])) {
            $this->processLLM();
        }

        if ($mode === 'csv') {
            $this->exportCsv();
        }

       


        $this->info('Finalizado.');
        return self::SUCCESS;
    }









    protected function collectAllVideos(): array
    {
        $all = [];

        foreach ($this->seedChannels() as $channel) {
            $this->info("Canal: {$channel['name']} ({$channel['handle']})");

            $channelId = $channel['youtube_channel_id'] ?? null;

            if (!$channelId) {
                #$channelId = $this->resolveChannelId($channel['handle']);
                $channelId = $this->resolveChannelId($channel['handle'], $channel['name']);
            }



            if (!$channelId) {
                $this->warn("Handle não resolvido para Channel ID: {$channel['handle']}");
                continue;
            }



            $channelDetails = $this->getCanaisDetailsByListCanaisIds([$channelId]);
            $channelData = $channelDetails[$channelId] ?? null;

            if (!$channelData || empty($channelData['channelDt'])) {
                $this->warn("Dados do canal incompletos: {$channel['name']}");
                continue;
            }

            $buckets = $this->makeBuckets(
                $channelData['channelDt'],
                now()->toIso8601String(),
                (int) $this->option('buckets')
            );

            foreach ($buckets as $bucket) {
                $this->line("Bucket {$bucket['start']} -> {$bucket['end']}");

                $ids = $this->searchVideos(
                    channelId: $channelId,
                    after: $bucket['start'],
                    before: $bucket['end'],
                    maxResults: (int) $this->option('limit')
                );

                if (!$ids) {
                    continue;
                }

                // pega detalhes em lote, chunk 50
                $details = $this->getVideoDetailsByListVideoIds($ids);

                foreach ($details as $video) {
                    if (empty($video['videoId'])) {
                        continue;
                    }

                    $all[$video['videoId']] = array_merge($video, [
                        'seed_name' => $channel['name'],
                        'seed_handle' => $channel['handle'],
                        'seed_dimension' => $channel['dimension'],
                        'seed_expected_label' => $channel['expected_label'],
                        'seed_language' => $channel['language'],

                        'channelId' => $channelId,
                        'channelDesc' => $channelData['channelDesc'] ?? null,
                        'channelKeywords' => $channelData['channelKeywords'] ?? [],
                        'channelSubs' => $channelData['channelSubs'] ?? null,
                        'channelViews' => $channelData['channelViews'] ?? null,
                        'channelVideos' => $channelData['channelVideos'] ?? null,

                        'bucket_start' => $bucket['start'],
                        'bucket_end' => $bucket['end'],
                    ]);
                }
            }
        }

        return array_values($all);
    }

   




protected function processLLM(): void
{
    if (!Storage::exists($this->videosFile)) {
        $this->error('Arquivo de vídeos não encontrado. Rode --mode=collect primeiro.');
        return;
    }

    $videos = json_decode(Storage::get($this->videosFile), true) ?? [];

    if (!$videos) {
        $this->warn('Nenhum vídeo encontrado.');
        return;
    }

    $labs = $this->getLabs();
    $labsKey = $this->labsKey($labs);

    $onlyTranscripts = (bool) $this->option('only-transcripts') || in_array('transcript', $labs, true);

    if ($onlyTranscripts) {
        $videos = array_values(array_filter($videos, function ($video) {
            $videoId = $video['videoId'] ?? null;
            return $videoId && $this->hasTranscript($videoId);
        }));
    }

    // ordena antes de processar para ficar sempre reprodutível
    usort($videos, fn ($a, $b) => strcmp($a['videoId'] ?? '', $b['videoId'] ?? ''));

    $processed = $this->getProcessedVideoIdsByLabs($labsKey);

    $pending = array_values(array_filter($videos, function ($video) use ($processed) {
        return !isset($processed[$video['videoId'] ?? '']);
    }));

    $max = (int) $this->option('max-llm');

    if ($max > 0) {
        $pending = array_slice($pending, 0, $max);
    }

    $this->info('Total vídeos elegíveis: ' . count($videos));
    $this->info('Labs: ' . $labsKey);
    $this->info('Já processados neste labs: ' . count($processed));
    $this->info('Pendentes nesta execução: ' . count($pending));

    foreach ($pending as $i => $video) {
        $this->line(($i + 1) . '/' . count($pending) . ' - processando ' . ($video['videoId'] ?? 'sem id'));

        try {
            $llm = retry(3, function () use ($video, $labs) {
                return $this->classifyWithLLM($video, $labs);
            }, 1000);

            $row = $this->buildResultRow($video, $llm, $labs);

            Storage::append(
                $this->resultsFile,
                json_encode($row, JSON_UNESCAPED_UNICODE)
            );

            $this->info('Salvo: ' . ($row['videoId'] ?? 'sem id'));
        } catch (\Throwable $e) {
            Log::error('Erro LLM benchmark', [
                'videoId' => $video['videoId'] ?? null,
                'labs' => $labsKey,
                'error' => $e->getMessage(),
            ]);

            $this->warn('Erro no vídeo: ' . ($video['videoId'] ?? 'sem id') . ' - ' . $e->getMessage());
        }

        usleep(300000);
    }
}









protected function getProcessedVideoIdsByLabs(string $labsKey): array
{
    if (!Storage::exists($this->resultsFile)) {
        return [];
    }

    $processed = [];
    $lines = explode("\n", trim(Storage::get($this->resultsFile)));

    foreach ($lines as $line) {
        if (!trim($line)) {
            continue;
        }

        $row = json_decode($line, true);

        if (!$row) {
            continue;
        }

        $rowLabs = $row['labs_key'] ?? null;

        // compatibilidade provisória com rodadas antigas
        if (!$rowLabs && isset($row['prompt_level'])) {
            $rowLabs = 'legacy_level_' . $row['prompt_level'];
        }

        if ($rowLabs !== $labsKey) {
            continue;
        }

        if (!empty($row['videoId'])) {
            $processed[$row['videoId']] = true;
        }
    }

    return $processed;
}





protected function buildResultRow(array $video, array $llm, array $labs): array
#protected function buildResultRow(array $video, array $llm, int $nivel): array
{
    $labsKey = $this->labsKey($labs);
    $video = $this->attachCategoryName($video);

    $expected = $video['seed_expected_label'] ?? null;
    $predicted = $llm['predicted_label'] ?? null;

    return [
        'videoId' => $video['videoId'] ?? null,
        'videoTitle' => $video['videoTitle'] ?? null,
        'seed_handle' => $video['seed_handle'] ?? null,
        'categoryId' => $video['videoCategId'] ?? null,
        'categoryName' => $video['categoryName'] ?? null,
        'language' => $video['seed_language'] ?? null,

        'dimension' => $video['seed_dimension'] ?? null,
        'expected_label' => $expected,

        'predicted_label' => $predicted,
        'hit' => ($expected && $predicted) ? (int) ($expected === $predicted) : null,


        #'prompt_level' => $nivel,
        'labs' => $labs,
        'labs_key' => $labsKey,
        

        'confidence' => $llm['confidence'] ?? null,
        #'polarization_level' => $llm['polarization_level'] ?? null,
        'is_ambiguous' => $llm['is_ambiguous'] ?? null,

        'intra_channel_deviation' => ($expected && $predicted && $expected !== $predicted) ? 1 : 0,
        'sentiment_valence' => $llm['sentiment_hint']['valence'] ?? null,
        'sentiment_intensity' => $llm['sentiment_hint']['emotional_intensity'] ?? null,

        'published' => $video['published'] ?? null,
        'viewCount' => $video['viewCount'] ?? null,
        'likeCount' => $video['likeCount'] ?? null,
        'commentCount' => $video['commentCount'] ?? null,
        'duration' => $video['duration'] ?? null,

        'seed_name' => $video['seed_name'] ?? null,

        'channelId' => $video['channelId'] ?? null,
        'channelTitle' => $video['channelTitle'] ?? null,

        'short_reason' => $llm['short_reason'] ?? null,
        'evidence' => $llm['evidence'] ?? [],

        'llm_raw' => $llm,

        'created_at' => now()->toDateTimeString(),
    ];
}



  protected function exportCsv(): void
{
    if (!Storage::exists($this->resultsFile)) {
        $this->error('Arquivo results.jsonl não encontrado.');
        return;
    }

    $lines = explode("\n", trim(Storage::get($this->resultsFile)));


    $rows = [];

    foreach ($lines as $line) {
        if (!trim($line)) {
            continue;
        }

        $row = json_decode($line, true);

        if (!$row) {
            continue;
        }

        $rows[] = $row;
    }

    usort($rows, fn ($a, $b) => strcmp($a['videoId'] ?? '', $b['videoId'] ?? ''));



    $headers = [
        'videoId',
        'videoTitle',
        'language',
        'seed_handle',

        'labs_key',
        'dimension',
        'expected_label',
        'predicted_label',
        'hit',


        'confidence',
        #'polarization_level',
        'is_ambiguous',
        'sentiment_valence',
        'sentiment_intensity',
        'intra_channel_deviation',

        'short_reason',
        'evidence',

        'categoryName',
        'published',
        'viewCount',
        'likeCount',
        'commentCount',
        'duration',
        'created_at',
    ];

    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $headers, ';');


    foreach ($rows as $row) {
        if (!$row || !is_array($row)) {
            continue;
        }

        fputcsv($handle, [
            $row['videoId'] ?? '',
            $row['videoTitle'] ?? '',
            $row['language'] ?? '',
            $row['seed_handle'] ?? '',

            $row['labs_key'] ?? '',

            $row['dimension'] ?? '',

            $row['expected_label'] ?? '',
            $row['predicted_label'] ?? '',
            $row['hit'] ?? '',

            $row['confidence'] ?? '',
            #$row['polarization_level'] ?? '',
            isset($row['is_ambiguous']) ? (int) $row['is_ambiguous'] : '',


            $row['sentiment_valence'] ?? '',
            $row['sentiment_intensity'] ?? '',
            $row['intra_channel_deviation'] ?? '',

            $row['short_reason'] ?? '',
            json_encode($row['evidence'] ?? [], JSON_UNESCAPED_UNICODE),

            $row['categoryName'] ?? '',
            $row['published'] ?? '',
            $row['viewCount'] ?? '',
            $row['likeCount'] ?? '',
            $row['commentCount'] ?? '',
            $row['duration'] ?? '',
            $row['created_at'] ?? '',
        ], ';');
    }


    rewind($handle);
    $content = stream_get_contents($handle);
    fclose($handle);

    Storage::put($this->csvFile, $content);

    $this->info('CSV salvo em: storage/app/' . $this->csvFile);
}




    protected function seedChannels(): array
    {
        return [
            ['name' => 'Meteoro Brasil', 'handle' => '@MeteoroBrasil', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'esquerda', 'language' => 'BR'],
            ['name' => 'Brasil Paralelo', 'handle' => '@brasilparalelo', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'direita', 'language' => 'BR'],
            ['name' => 'TV 247', 'handle' => '@tv247', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'esquerda', 'language' => 'BR'],
            ['name' => 'Jovem Pan News', 'handle' => '@JovemPanNews', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'direita', 'language' => 'BR'],
            ['name' => 'Democracy Now', 'handle' => '@democracynow', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'esquerda', 'language' => 'US'],
            ['name' => 'Fox News', 'handle' => '@FoxNews', 'youtube_channel_id' => null, 'dimension' => 'politica', 'expected_label' => 'direita', 'language' => 'US'],
            ['name' => 'Padre Paulo Ricardo', 'handle' => '@padrepauloricardo', 'youtube_channel_id' => null, 'dimension' => 'religiao', 'expected_label' => 'cristao', 'language' => 'BR'],
            ['name' => 'Umbanda EAD', 'handle' => '@UmbandaEAD', 'youtube_channel_id' => null, 'dimension' => 'religiao', 'expected_label' => 'afro-brasileiro', 'language' => 'BR'],
            ['name' => 'Islam Brasil', 'handle' => '@islambrasil', 'youtube_channel_id' => null, 'dimension' => 'religiao', 'expected_label' => 'islamico', 'language' => 'BR'],
            ['name' => 'Pirula', 'handle' => '@Pirula25', 'youtube_channel_id' => null, 'dimension' => 'religiao', 'expected_label' => 'ateu-secular', 'language' => 'BR'],
            ['name' => 'Nerdologia', 'handle' => '@Nerdologia', 'youtube_channel_id' => null, 'dimension' => 'ciencia', 'expected_label' => 'cientifico', 'language' => 'BR'],
            ['name' => 'Ciência Todo Dia', 'handle' => '@CienciaTodoDia', 'youtube_channel_id' => null, 'dimension' => 'ciencia', 'expected_label' => 'cientifico', 'language' => 'BR'],
            ['name' => 'Globebusters', 'handle' => '@Globebusters', 'youtube_channel_id' => null, 'dimension' => 'ciencia', 'expected_label' => 'conspiratorio', 'language' => 'US'],
            ['name' => 'The Higherside Chats', 'handle' => '@TheHighersideChats', 'youtube_channel_id' => null, 'dimension' => 'ciencia', 'expected_label' => 'conspiratorio', 'language' => 'US'],
            ['name' => 'Dr John Campbell', 'handle' => '@Campbellteaching', 'youtube_channel_id' => null, 'dimension' => 'ciencia', 'expected_label' => 'anti-ciencia', 'language' => 'US'],
        ];
    }

   
    


    protected function resolveChannelId(string $handle, ?string $name = null): ?string
    {
        $apiKey = env('YOUTUBE_API_KEY');

        if (!$apiKey) {
            $this->error('YOUTUBE_API_KEY vazia no .env');
            return null;
        }

        $clean = ltrim(trim($handle), '@');

        // 1) tenta SEM @ no forHandle
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'key' => $apiKey,
                'part' => 'id',
                'forHandle' => $clean,
            ]);

        if (!$response->successful()) {
            $this->error("Erro forHandle {$clean}: HTTP {$response->status()}");
            $this->line($response->body());
        } else {
            $id = $response->json('items.0.id');
            if ($id) return $id;

            $this->warn("Sem items via forHandle: {$clean}");
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 2) fallback pelo nome do canal
        if ($name) {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get('https://www.googleapis.com/youtube/v3/search', [
                    'key' => $apiKey,
                    'part' => 'snippet',
                    'q' => $name,
                    'type' => 'channel',
                    'maxResults' => 1,
                ]);

            if (!$response->successful()) {
                $this->error("Erro search {$name}: HTTP {$response->status()}");
                $this->line($response->body());
                return null;
            }

            $id = $response->json('items.0.id.channelId');
            if ($id) return $id;

            $this->warn("Sem items via search: {$name}");
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return null;
    }






    public function getCanaisDetailsByListCanaisIds(array $channelIds): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        $channelIds = array_values(array_unique(array_filter($channelIds)));
        if (!$channelIds) return [];

        $out = [];

        foreach (array_chunk($channelIds, 50) as $pack) {
            $params = [
                'key' => $apiKey,
                'id' => implode(',', $pack),
                'part' => 'snippet,statistics,brandingSettings,contentDetails',
            ];

            $resp = file_get_contents('https://www.googleapis.com/youtube/v3/channels?' . http_build_query($params));
            $json = json_decode($resp, true) ?: [];

            foreach ($json['items'] ?? [] as $ch) {
                $id = $ch['id'] ?? null;
                if (!$id) continue;

                $sn = $ch['snippet'] ?? [];
                $st = $ch['statistics'] ?? [];
                $br = $ch['brandingSettings']['channel'] ?? [];

                $out[$id] = [
                    'channelId' => $id,
                    'channelTitle' => $sn['title'] ?? null,
                    'channelDesc' => $sn['description'] ?? null,
                    'channelDt' => $sn['publishedAt'] ?? null,
                    'channelCountry' => $sn['country'] ?? null,
                    'channelHandle' => $sn['customUrl'] ?? null,
                    'channelSubs' => isset($st['subscriberCount']) ? (int) $st['subscriberCount'] : null,
                    'channelViews' => isset($st['viewCount']) ? (int) $st['viewCount'] : null,
                    'channelVideos' => isset($st['videoCount']) ? (int) $st['videoCount'] : null,
                    'channelKeywords' => $this->parseBrandingKeywords($br['keywords'] ?? ''),
                ];
            }
        }

        return $out;
    }

    public function getVideoDetailsByListVideoIds(array $ids): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) return [];

        $out = [];

        foreach (array_chunk($ids, 50) as $pack) {
            $params = [
                'key' => $apiKey,
                'id' => implode(',', $pack),
                'part' => 'snippet,statistics,contentDetails',
            ];

            $res = file_get_contents('https://www.googleapis.com/youtube/v3/videos?' . http_build_query($params));
            $json = json_decode($res, true) ?: [];

            foreach ($json['items'] ?? [] as $v) {
                $sn = $v['snippet'] ?? [];
                $st = $v['statistics'] ?? [];
                $cd = $v['contentDetails'] ?? [];

                $out[] = [
                    'videoId' => $v['id'] ?? null,
                    'channelId' => $sn['channelId'] ?? null,
                    'channelTitle' => $sn['channelTitle'] ?? null,
                    'published' => $sn['publishedAt'] ?? null,
                    'videoTitle' => $sn['title'] ?? '',
                    'videoDesc' => $sn['description'] ?? null,
                    'videoTags' => $sn['tags'] ?? [],
                    'videoCategId' => $sn['categoryId'] ?? null,
                    'lang' => $sn['defaultAudioLanguage'] ?? ($sn['defaultLanguage'] ?? null),
                    'thumbnail' => $sn['thumbnails']['default']['url'] ?? null,
                    'viewCount' => isset($st['viewCount']) ? (int) $st['viewCount'] : null,
                    'likeCount' => isset($st['likeCount']) ? (int) $st['likeCount'] : null,
                    'commentCount' => isset($st['commentCount']) ? (int) $st['commentCount'] : null,
                    'duration' => $this->ISO8601ToSeconds($cd['duration'] ?? null),
                ];
            }
        }

        usort($out, fn($a, $b) => strcmp($a['published'] ?? '', $b['published'] ?? ''));

        return $out;
    }

    protected function searchVideos(string $channelId, string $after, string $before, int $maxResults): array
    {
        $response = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'channelId' => $channelId,
            'type' => 'video',
            'order' => 'date',
            'publishedAfter' => $after,
            'publishedBefore' => $before,
            'maxResults' => min($maxResults, 50),
            'key' => env('YOUTUBE_API_KEY'),
        ]);

        return collect($response->json('items', []))
            ->pluck('id.videoId')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function makeBuckets(string $start, string $end, int $parts): array
    {
        $startTs = strtotime($start);
        $endTs = strtotime($end);

        if (!$startTs || !$endTs || $endTs <= $startTs) {
            return [];
        }

        $step = intval(($endTs - $startTs) / $parts);
        $buckets = [];

        for ($i = 0; $i < $parts; $i++) {
            $a = $startTs + ($step * $i);
            $b = $i === $parts - 1 ? $endTs : $startTs + ($step * ($i + 1));

            $buckets[] = [
                'start' => gmdate('c', $a),
                'end' => gmdate('c', $b),
            ];
        }

        return $buckets;
    }

   



protected function classifyWithLLM(array $video, array $labs): array
{
    $prompt = $this->makePrompt($video, $labs);

    $this->savePromptLog($video, $labs, $prompt);

    $response = Http::withToken(env('OPENAI_API_KEY'))
        ->timeout(120)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1-mini',
            'temperature' => 0,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um classificador acadêmico de orientação discursiva. Responda apenas em JSON válido.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

    if (!$response->successful()) {
        throw new \RuntimeException($response->body());
    }

    $content = $response->json('choices.0.message.content');

    return json_decode($content, true) ?: [
        'error' => true,
        'raw' => $content,
    ];
}







    ##################################################################



    protected function getPolosDaPolarizacao(string $dimensao): array
    {
        return match ($dimensao) {
            'politica' => ['esquerda', 'centro', 'direita', 'indefinido'],
            'religiao' => ['cristao', 'afro-brasileiro', 'islamico', 'ateu-secular', 'indefinido'],
            'ciencia' => ['cientifico', 'conspiratorio', 'anti-ciencia', 'indefinido'],
            default => ['indefinido'],
        };
    }



protected function getYoutubeCategories(): array
{
    return [
        1  => 'Film & Animation',
        2  => 'Autos & Vehicles',
        10 => 'Music',
        15 => 'Pets & Animals',
        17 => 'Sports',
        18 => 'Short Movies',
        19 => 'Travel & Events',
        20 => 'Gaming',
        21 => 'Videoblogging',
        22 => 'People & Blogs',
        23 => 'Comedy',
        24 => 'Entertainment',
        25 => 'News & Politics',
        26 => 'Howto & Style',
        27 => 'Education',
        28 => 'Science & Technology',
        29 => 'Nonprofits & Activism',
        30 => 'Movies',
        31 => 'Anime/Animation',
        32 => 'Action/Adventure',
        33 => 'Classics',
        34 => 'Comedy (legacy)',
        35 => 'Documentary',
        36 => 'Drama',
        37 => 'Family',
        38 => 'Foreign',
        39 => 'Horror',
        40 => 'Sci-Fi/Fantasy',
        41 => 'Thriller',
        42 => 'Shorts',
        43 => 'Shows',
        44 => 'Trailers',
    ];
}

protected function attachCategoryName(array $video): array
{
    $map = $this->getYoutubeCategories();

    $id = (int) ($video['videoCategId'] ?? 0);

    $video['categoryName'] = $map[$id] ?? 'Unknown';

    return $video;
}



protected function savePromptLog(array $video, array $labs, string $prompt): void
{
    Storage::makeDirectory($this->promptsDir);

    $videoId = $video['videoId'] ?? 'sem_id';
    $labsKey = $this->labsKey($labs);

    $payload = [
        'videoId' => $videoId,
        'labs' => $labs,
        'labs_key' => $labsKey,
        'seed_name' => $video['seed_name'] ?? null,
        'expected_label' => $video['seed_expected_label'] ?? null,
        'videoTitle' => $video['videoTitle'] ?? null,
        'prompt' => $prompt,
        'created_at' => now()->toDateTimeString(),
    ];

    Storage::put(
        "{$this->promptsDir}/{$labsKey}_{$videoId}.json",
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}


protected function getTranscriptText(string $videoId, int $maxChars = 20000): ?string
{
    $path = "polarization/transcripts/{$videoId}.json";

    if (!Storage::exists($path)) {
        return null;
    }

    $json = json_decode(Storage::get($path), true) ?: [];

    $text = $json['plain_text'] ?? null;

    if (!$text && isset($json['raw']['transcripts'])) {
        $parts = [];

        foreach ($json['raw']['transcripts'] as $item) {
            if (!empty($item['text'])) {
                $parts[] = $item['text'];
            }
        }

        $text = trim(preg_replace('/\s+/', ' ', implode(' ', $parts)));
    }

    if (!$text) {
        return null;
    }

    return mb_substr(trim($text), 0, $maxChars);
}


  
    



protected function buildVideoInputForPrompt(array $video, array $labs): string
{
    $video = $this->attachCategoryName($video);
    $videoId = $video['videoId'] ?? null;

    $tags = implode(', ', $video['videoTags'] ?? []);
    $hashtags = $this->extractHashtags(
        ($video['videoTitle'] ?? '') . "\n" . ($video['videoDesc'] ?? '')
    );

    $parts = [];

    // BASE SEMPRE OBRIGATÓRIA
    $parts[] = "[BASE]";
    $parts[] = "Título do vídeo: " . ($video['videoTitle'] ?? '');
    $parts[] = "Idioma esperado: " . ($video['seed_language'] ?? '');

    if (in_array('video', $labs, true)) {
        $parts[] = "\n[VIDEO]";
        $parts[] = "Descrição do vídeo: " . ($video['videoDesc'] ?? '');
        $parts[] = "Categoria YouTube: " . ($video['categoryName'] ?? 'Unknown');
        $parts[] = "Tags/keywords do vídeo: " . $tags;
        $parts[] = "Hashtags: " . $hashtags;
    }

    if (in_array('stats', $labs, true)) {
        $parts[] = "\n[STATS]";
        $parts[] = "Visualizações: " . ($video['viewCount'] ?? '');
        $parts[] = "Likes: " . ($video['likeCount'] ?? '');
        $parts[] = "Comentários: " . ($video['commentCount'] ?? '');
        $parts[] = "Duração em segundos: " . ($video['duration'] ?? '');
    }

    if (in_array('channel', $labs, true)) {
        $parts[] = "\n[CHANNEL]";
        $parts[] = "Nome do canal: " . ($video['channelTitle'] ?? $video['seed_name'] ?? '');
        $parts[] = "Descrição do canal: " . ($video['channelDesc'] ?? '');
        $parts[] = "Keywords do canal: " . implode(', ', $video['channelKeywords'] ?? []);
        $parts[] = "Inscritos do canal: " . ($video['channelSubs'] ?? '');
        $parts[] = "Views totais do canal: " . ($video['channelViews'] ?? '');
        $parts[] = "Total de vídeos do canal: " . ($video['channelVideos'] ?? '');
    }

    if (in_array('transcript', $labs, true)) {
        $transcript = $videoId ? $this->getTranscriptText($videoId) : null;

        $parts[] = "\n[TRANSCRIPT]";
        $parts[] = "Transcrição do vídeo:";
        $parts[] = $transcript ?: '[transcrição não disponível]';
    }

    return implode("\n", $parts);
}









protected function makePrompt(array $video, array $labs): string
{
    $dimensao = $video['seed_dimension'] ?? 'indefinido';
    $opcoes = implode(', ', $this->getPolosDaPolarizacao($dimensao));
    $labsKey = $this->labsKey($labs);
    $input = $this->buildVideoInputForPrompt($video, $labs);

    return <<<PROMPT
        Você é um classificador acadêmico de orientação discursiva em vídeos do YouTube.

        A dimensão analítica esperada é: {$dimensao}

        Configuração de dados usada neste teste: {$labsKey}

        Classifique o VÍDEO, não o canal, apenas entre estas opções:
        {$opcoes}

        ---------------------------------------
        REGRAS METODOLÓGICAS
        ---------------------------------------

        1. Use exclusivamente os dados fornecidos.
        2. Não utilize conhecimento externo sobre o canal, o criador ou o veículo.
        3. Não utilize o rótulo esperado do benchmark.
        4. Classifique o enquadramento discursivo do vídeo, não apenas o tema.
        5. Não classifique com base na reputação geral do canal.
        6. A categoria do YouTube é apenas informação auxiliar, nunca decisiva.

        ---------------------------------------
        HIERARQUIA DE EVIDÊNCIA
        ---------------------------------------

        Priorize nesta ordem:

        1. Transcrição do vídeo, quando disponível.
        2. Descrição do vídeo, quando disponível.
        3. Título do vídeo.
        4. Tags, hashtags e categoria, quando disponível.

        Se houver conflito entre título/descrição e transcrição, a transcrição deve prevalecer.

        ---------------------------------------
        REGRAS DE DECISÃO
        ---------------------------------------

        - Classifique apenas se houver evidência discursiva explícita.
        - Se o conteúdo for apenas informativo, descritivo, histórico, jornalístico ou neutro, use "indefinido".
        - Se houver sinais conflitantes, escolha um polo apenas quando houver predominância clara.
        - Se os sinais forem equilibrados ou insuficientes, use "indefinido".
        - Não infira intenção ideológica implícita sem evidência textual.
        - Não confunda crítica, descrição histórica ou discussão acadêmica com posicionamento polarizado.

        ---------------------------------------
        DEFINIÇÕES ANALÍTICAS PARA CIÊNCIA
        ---------------------------------------

        Quando a dimensão for "ciencia":

        - "cientifico": conteúdo alinhado com evidências, consenso científico, método científico, explicação educacional ou análise acadêmica.
        - "anti-ciencia": conteúdo que rejeita, desacredita ou nega consenso científico sem base robusta.
        - "conspiratorio": conteúdo que sustenta narrativa conspiratória, causalidade oculta, agentes secretos, manipulação deliberada ou explicação alternativa não verificável.
        - "indefinido": conteúdo descritivo, histórico, jornalístico, cultural ou sem posição clara sobre ciência/conspiração.

        Importante:
        - Só use "conspiratorio" se o vídeo defender ou promover uma narrativa conspiratória como explicação central.

        ---------------------------------------
        DEFINIÇÕES ANALÍTICAS PARA POLÍTICA
        ---------------------------------------

        Quando a dimensão for "politica":

        - "esquerda": enquadramento favorável a pautas progressistas, redistributivas, trabalhistas, igualitárias, críticas ao conservadorismo ou à direita.
        - "direita": enquadramento favorável a pautas conservadoras, liberais, nacionalistas, religiosas conservadoras, anticomunistas, antipetistas ou críticas à esquerda.
        - "centro": enquadramento moderado, institucional, tecnocrático ou sem polarização clara.
        - "indefinido": conteúdo factual ou jornalístico sem posição dominante clara.

        ---------------------------------------
        DEFINIÇÕES ANALÍTICAS PARA RELIGIÃO
        ---------------------------------------

        Quando a dimensão for "religiao":

        - "cristao": enquadramento associado a doutrina, prática, defesa ou visão cristã.
        - "afro-brasileiro": enquadramento associado a religiões de matriz africana ou afro-brasileira.
        - "islamico": enquadramento associado ao Islã, cultura islâmica ou prática muçulmana.
        - "ateu-secular": enquadramento crítico à religião, secularista, ateísta ou laico.
        - "indefinido": conteúdo religioso descritivo, histórico ou sem posição clara.

        ---------------------------------------
        AMBIGUIDADE
        ---------------------------------------

        Use is_ambiguous = true quando:

        - houver sinais mistos relevantes;
        - houver evidência insuficiente;
        - o vídeo tratar do tema sem assumir posição clara;
        - título, descrição e transcrição apontarem em direções diferentes.

        Use is_ambiguous = false quando houver evidência consistente para a classificação escolhida.

        ---------------------------------------
        EVIDÊNCIA OBRIGATÓRIA
        ---------------------------------------

        No campo evidence:

        - liste 1 ou 2 evidências curtas;
        - use trechos literais ou quase literais dos dados fornecidos;
        - não invente evidência;
        - não use conhecimento externo;
        - se não houver evidência suficiente, explique isso no short_reason.

        ---------------------------------------
        INTERPRETAÇÃO DOS CAMPOS
        ---------------------------------------

        confidence:
        - 0.80 a 1.00 = evidência clara e consistente.
        - 0.60 a 0.79 = evidência moderada.
        - abaixo de 0.60 = baixa segurança.


        sentiment_hint.valence:
        - "positivo" quando o tom predominante for favorável, celebratório ou afirmativo.
        - "negativo" quando o tom predominante for crítico, hostil, alarmista ou acusatório.
        - "neutro" quando o tom for informativo/descritivo.
        - "indefinido" quando não houver evidência suficiente.

        sentiment_hint.emotional_intensity:
        - 0.00 a 0.20 = baixa carga emocional.
        - 0.21 a 0.50 = carga emocional leve.
        - 0.51 a 0.75 = carga emocional moderada.
        - 0.76 a 1.00 = carga emocional alta.

        ---------------------------------------
        DADOS DISPONÍVEIS
        ---------------------------------------

        {$input}

        ---------------------------------------
        FORMATO DE RESPOSTA
        ---------------------------------------

        Responda exclusivamente em JSON válido, sem markdown, sem comentários e sem texto fora do JSON:

        {
        "dimension": "{$dimensao}",
        "predicted_label": "",
        "confidence": 0.0,
        "is_ambiguous": false,
        "evidence": [],
        "short_reason": "",
        "sentiment_hint": {
            "valence": "positivo|neutro|negativo|indefinido",
            "emotional_intensity": 0.0
        }
    }
PROMPT;
}




    protected function extractHashtags(string $text): string
    {
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $text, $matches);
        return implode(', ', $matches[1] ?? []);
    }

    protected function parseBrandingKeywords($keywords): array
    {
        if (is_array($keywords)) {
            return $keywords;
        }

        if (!$keywords) {
            return [];
        }

        preg_match_all('/"([^"]+)"|(\S+)/', $keywords, $matches);

        return array_values(array_filter(array_map(function ($a, $b) {
            return $a ?: $b;
        }, $matches[1], $matches[2])));
    }

    protected function ISO8601ToSeconds(?string $duration): int
    {
        if (!$duration) return 0;

        try {
            $interval = new \DateInterval($duration);
            return ($interval->d * 86400)
                + ($interval->h * 3600)
                + ($interval->i * 60)
                + $interval->s;
        } catch (\Throwable) {
            return 0;
        }
    }
}