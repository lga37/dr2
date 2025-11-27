<?php

namespace App\Livewire;

use Log;
use App\Traits\Comum;
use App\Models\Tarefa;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use App\Services\YoutubeStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;


class Tarefa2 extends Component
{

    use Comum;


    function getTipoTarefa(): string
    {
        return 't2';
    }

    public array $canais = [];

    public $buscas = [];
    public array $videos_dos_canais = [];
    public array $selecionados = [];
    public string $addInput = '';
    public ?string $maisPolarizado = null;
    public array $polarizMediaArray = [];
    public ?string $maisPolarizadoReal = null;
    public ?bool $acertou = null;
    public string $feedback = '';       // textarea
    public bool $mostrarAvaliacao = false;
    public bool $mostrarFeedback = false;

    // opcional: extraia isso pra um trait e reutilize nos dois componentes
    protected array $sessionPrefixes = ['t2_query', 't2_canais']; // ajuste como preferir


    public array $chart = [];
    public array $samples = [];          // [canal => [amostras]]
    public array $canalMedias = [];      // [canal => média -100..+100]
    public ?string $canalMaisPolar = null;

    public function mount()
    {
        $this->selecionados   = Session::get('t2_canais', []);
        $this->query = Session::get('t2_query', '');

        $this->avaliarCanaisGpt();
    }



    public function avaliarCanaisGpt(): void
    {
        $selecionados = Session::get('t2_canais', []); // [channelId => ['channelDt','channelVideos','title'...]]
        if (!$selecionados)
            return;

        $todosVideos = []; // [canal => [rows]]

        // 1) Coleta/armazenamento por canal
        foreach ($selecionados as $channelId => $raw) {
            $bucketed = $this->getChannelVideosByBuckets($channelId, (int) $raw['channelVideos'], $raw['channelDt'], 10, false);

            // lista única de vídeos desse canal
            $videos = $bucketed['flattened'] ?? [];
            if (!$videos)
                continue;

            // 2) Calcular polarização de título/descrição e upsert
            $videos = $this->annotatePolarAndUpsert($videos, $channelId);

            // 3) Guardar em memória p/ etapa de gráfico/samples
            $todosVideos[$channelId] = $videos;
        }

        if (!$todosVideos)
            return;

        // 4) Preparar dados para gráfico (X = linha do tempo absoluta)
        $this->buildChartPolarizacao($selecionados, $todosVideos);

        // 5) Escolher canal mais polarizado (maior |média|)
        $this->canalMaisPolar = $this->pickCanalMaisPolar($this->canalMedias);
    }


    /** Converte -1..+1 -> -100..+100 */
    protected function toPercentPol(?float $s): ?float
    {
        if (!is_numeric($s))
            return null;
        $p = max(-100, min(100, round($s * 100, 2)));
        return $p;
    }

    /**
     * Dummy de sentimento: devolve [-1..+1] de forma determinística
     * baseado no conteúdo + uma semente (ex.: videoId e "t"/"d").
     */
    protected function sentimentScore(string $text, ?string $seed = null): ?float
    {
        $text = trim($text);
        if ($text === '') return null;

        // hash => 0..1
        $h = md5(($seed ?? '') . '|' . $text);
        $u = hexdec(substr($h, 0, 8)) / 0xFFFFFFFF; // 0..1

        // mapeia p/ -1..+1
        return (float) (-1.0 + 2.0 * $u);
    }


    /** Calcula polarização de título/descrição, faz upsert e devolve array anotado. */
    protected function annotatePolarAndUpsert(array $videos, string $channelId): array
    {
        // 1) anotar polarização
        foreach ($videos as &$v) {
            $title = (string)($v['title'] ?? '');
            $desc  = (string)($v['desc'] ?? '');
            #$pTitle = $this->toPercentPol($this->sentimentScore($title));
            #$pDesc  = $this->toPercentPol($this->sentimentScore($desc));

            $pTitle = $this->toPercentPol($this->sentimentScore($title, ($v['videoId'] ?? '') . ':t'));
            $pDesc  = $this->toPercentPol($this->sentimentScore($desc, ($v['videoId'] ?? '') . ':d'));


            // média do vídeo (se ambos existirem); se só um existir, usa o que tem
            $pMix = null;
            if (is_numeric($pTitle) && is_numeric($pDesc))
                $pMix = round(($pTitle + $pDesc) / 2, 2);
            elseif (is_numeric($pTitle))
                $pMix = $pTitle;
            elseif (is_numeric($pDesc))
                $pMix = $pDesc;

            $v['polar_title'] = $pTitle;
            $v['polar_desc']  = $pDesc;
            $v['polar_mix']   = $pMix;
        }
        unset($v);

        // 2) upsert no banco (exemplo com Eloquent)
        // Estrutura sugerida da tabela `videos`:
        // - video_id (unique), channel_id, title, description, published_at (datetime)
        // - polar_title, polar_desc, polar_mix (float/nullable)
        // Ajuste o Model/colunas conforme seu schema.

        $rows = array_map(function ($v) use ($channelId) {
            return [
                'video_id'     => $v['videoId'],
                'channel_id'   => $channelId,
                'title'        => $v['title'] ?? '',
                'description'  => $v['desc'] ?? '',
                'published_at' => isset($v['publishedAt']) ? Carbon::parse($v['publishedAt']) : null,
                'polar_title'  => $v['polar_title'],
                'polar_desc'   => $v['polar_desc'],
                'polar_mix'    => $v['polar_mix'],
                'updated_at'   => now(),
                'created_at'   => now(),
            ];
        }, $videos);

        // Exemplo de upsert (ajuste o Model/colunas conforme seu projeto)
        // Video::upsert($rows, ['video_id'], ['title','description','published_at','polar_title','polar_desc','polar_mix','updated_at']);

        return $videos;
    }

    /** Amostra N itens aleatórios de um array. */
    protected function sample(array $arr, int $n = 10): array
    {
        if (count($arr) <= $n)
            return array_values($arr);
        $idx = array_rand($arr, $n);
        if (!is_array($idx))
            $idx = [$idx];
        return array_values(array_intersect_key($arr, array_flip($idx)));
    }

    /** Escolhe o canal mais polarizado por valor absoluto da média (-100..+100). */
    protected function pickCanalMaisPolar(array $medias): ?string
    {
        $valid = array_filter($medias, 'is_numeric');
        if (!$valid)
            return null;
        $bestId = null;
        $bestAbs = -1;
        foreach ($medias as $id => $m) {
            if (!is_numeric($m))
                continue;
            $abs = abs($m);
            if ($abs > $bestAbs) {
                $bestAbs = $abs;
                $bestId = $id;
            }
        }
        return $bestId;
    }


    /** Constrói $this->chart, $this->samples, $this->canalMedias */
    protected function buildChartPolarizacao(array $selecionados, array $todosVideos): void
    {
        // 1) menor publishedAt entre todos os canais
        $globalStart = null;
        foreach ($todosVideos as $vids) {
            foreach ($vids as $v) {
                if (empty($v['publishedAt']))
                    continue;
                $d = Carbon::parse($v['publishedAt'])->startOfDay();
                $globalStart = $globalStart ? min($globalStart, $d) : $d;
            }
        }
        if (!$globalStart) $globalStart = now()->startOfDay();

        // helpers
        $addDays = fn(Carbon $d, int $n) => $d->copy()->addDays($n);

        $globalMin = PHP_INT_MAX;
        $globalMax = PHP_INT_MIN;
        $series = [];
        $medias = [];

        foreach ($todosVideos as $channelId => $videos) {
            $titlePoints = [];
            $descPoints  = [];
            $sum = 0;
            $n = 0; // para média do canal (polar_mix)

            $localMin = PHP_INT_MAX;
            $localMax = PHP_INT_MIN;

            foreach ($videos as $v) {
                if (!empty($v['publishedAt'])) {
                    $x = max(0, $globalStart->diffInDays(Carbon::parse($v['publishedAt'])->startOfDay()));
                    $localMin = min($localMin, $x);
                    $localMax = max($localMax, $x);

                    if (is_numeric($v['polar_title'])) {
                        $titlePoints[] = [
                            'x' => $x,
                            'y' => (float) $v['polar_title'],   // já -100..+100
                            'label' => mb_substr($v['title'] ?? '', 0, 20)
                        ];
                    }
                    if (is_numeric($v['polar_desc'])) {
                        $descPoints[] = [
                            'x' => $x,
                            'y' => (float) $v['polar_desc'],
                            'label' => mb_substr($v['desc'] ?? '', 0, 20)
                        ];
                    }

                    if (is_numeric($v['polar_mix'])) {
                        $sum += $v['polar_mix'];
                        $n++;
                    }
                }
            }

            if ($localMin === PHP_INT_MAX) {
                $localMin = 0;
                $localMax = 0;
            }
            $globalMin = min($globalMin, $localMin);
            $globalMax = max($globalMax, $localMax);

            $media = $n ? round($sum / $n, 2) : null;
            $medias[$channelId] = $media;

            #dd($selecionados);

            $series[$channelId] = [
                'title'        => $selecionados[$channelId]['channelTitle'] ?? $channelId,
                'points_title' => $titlePoints,
                'points_desc'  => $descPoints,
                'avg'          => $media,          // para linha horizontal
                'startDay'     => $localMin,
                'endDay'       => $localMax,
            ];

            // samples p/ tabela
            $this->samples[$channelId] = $this->sample($videos, 12);
        }

        if ($globalMin === PHP_INT_MAX) {
            $globalMin = 0;
            $globalMax = 0;
        }

        $this->canalMedias = $medias;
        $this->chart = [
            'globalStart' => $globalStart->toIso8601String(),
            'min'         => $globalMin,
            'max'         => $globalMax,
            'series'      => $series,
        ];
    }







    ###################################################
    protected function getChannelVideosByBuckets(
        string $channelId,
        int $channelVideos,          // total do canal
        string $channelDtIso,        // início da janela (ex.: 1º vídeo)
        int $maxBuckets = 10,
        bool $forceRefresh = false
    ): array {
        $channelId = trim($channelId);
        if ($channelId === '' || $channelVideos < 0) return [];

        $apiKey = config('services.youtube.key') ?? env('YOUTUBE_API_KEY');
        $baseS  = 'https://www.googleapis.com/youtube/v3/search';

        $start = Carbon::parse($channelDtIso);
        $now   = now();

        // buckets = teto(vídeos/50), limitado a 10
        $buckets = max(1, min($maxBuckets, (int) ceil($channelVideos / 50)));

        // chave de cache diária
        $cacheKey = sprintf(
            'yt:channel:videos:buckets:v3:%s:%s:%d:%d',
            $channelId,
            $start->toDateString(),
            $buckets,
            (int) floor($now->timestamp / 86400)
        );

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addDay(), function () use ($channelId, $channelVideos, $start, $now, $buckets, $apiKey, $baseS) {
            Log::info('[YT] cache MISS', ['channelId' => $channelId, 'buckets' => $buckets]);

            // janelas iguais no tempo
            $wins = [];
            $span = max(1, $start->diffInSeconds($now));
            for ($i = 0; $i < $buckets; $i++) {
                $after  = $start->copy()->addSeconds((int) floor($span * ($i / $buckets)));
                $before = $start->copy()->addSeconds((int) floor($span * (($i + 1) / $buckets)));
                $wins[] = ['after' => $after, 'before' => $before];
            }

            $outBuckets = [];
            $flat = [];
            $seen = [];

            foreach ($wins as $i => $w) {
                $params = [
                    'key'            => $apiKey,
                    'part'           => 'snippet',   // único permitido no search.list
                    'channelId'      => $channelId,
                    'type'           => 'video',
                    'order'          => 'relevance', // pedido
                    'maxResults'     => 50,          // pedido (fixo)
                    // RFC3339 Zulu (…Z)
                    'publishedAfter'  => $w['after']->toIso8601ZuluString(),
                    'publishedBefore' => $w['before']->toIso8601ZuluString(),
                ];

                $url = $baseS . '?' . http_build_query($params);
                Log::info(sprintf('[YT] search.list bucket %d/%d', $i + 1, $buckets), ['url' => $url]);

                $resp  = file_get_contents($url);
                if ($resp === false) continue;

                $data  = json_decode($resp ?: '[]', true);
                $items = $data['items'] ?? [];

                $rows = [];
                foreach ($items as $it) {
                    $vid = $it['id']['videoId'] ?? null;
                    $sn  = $it['snippet'] ?? [];
                    if (!$vid || isset($seen[$vid])) continue;
                    $seen[$vid] = true;

                    $rows[] = [
                        'videoId'     => $vid,
                        'title'       => (string) ($sn['title'] ?? ''),
                        'desc'        => (string) ($sn['description'] ?? ''),
                        'publishedAt' => $sn['publishedAt'] ?? ($sn['publishTime'] ?? null), // manter para o gráfico
                        'channelId'   => $sn['channelId'] ?? $channelId,
                        'channelTitle' => $sn['channelTitle'] ?? null,
                        // slots para futuras análises
                        'tags'        => null,
                        'duration'    => null,
                        'stats'       => null,
                        'polar'       => null,
                    ];
                }

                // ordena por data asc dentro do bucket (opcional)
                usort($rows, fn($a, $b) => strcmp($a['publishedAt'] ?? '', $b['publishedAt'] ?? ''));

                $outBuckets[] = [
                    'bucket'          => $i + 1,
                    'publishedAfter'  => $w['after']->toIso8601ZuluString(),
                    'publishedBefore' => $w['before']->toIso8601ZuluString(),
                    'items'           => array_values($rows),
                ];

                $flat = array_merge($flat, $rows);
            }

            return [
                'buckets'   => $outBuckets,
                'flattened' => $flat,
                'meta'      => [
                    'channelId'    => $channelId,
                    'start'        => $start->toIso8601ZuluString(),
                    'end'          => $now->toIso8601ZuluString(),
                    'buckets'      => $buckets,
                    'perBucket'    => 50,
                    'totalFetched' => count($flat),
                    'order'        => 'relevance',
                ],
            ];
        });
    }







    # usuario escolheu o mais toxico
    public function escolherMaisPolarizado(string $canalId): void
    {
        if (!isset($this->selecionados[$canalId])) return;

        $this->maisPolarizado = $canalId;
        Session::put('tarefa2_mais_polarizado', $canalId);

        // limpa resultado anterior se o usuário mudar de ideia
        $this->acertou = null;
    }



    public function validarTarefa2()
    {
        // carrega seleção e zera memória local
        $this->selecionados = Session::get('t2_canais', $this->selecionados);
        $this->mostrarFeedback = true;


        if (!$this->maisPolarizado) {
            $this->msg('Vc deve selecionar um registro');
            return;
        }
        // reset do cache desta tela (opcional)
        Session::forget('t2_videos');
        $sessVideos = [];
        #$tarefa = $this->getTarefa('T2');
        #$storage = app(YoutubeStorage::class);

        #dump($this->selecionados);

        foreach ($this->selecionados as $canalId => $raw) {

            #atencao aqui to usando q
            $q = $raw['q'] ?? '[erro]';
            $buscaBD = $this->upsertBusca($q);
            #dump($buscaBD);

            $ch = [
                'youtube_id' => $raw['channelId'],
                'nome'       => $raw['channelTitle'] ?? null,
                'keywords'   => $raw['channelKeywords'] ?? [],
                'handle'     => $raw['channelHandle'] ?? null,
                'inscritos'  => $raw['channelSubs'] ?? null,
                'views'      => $raw['channelViews'] ?? null,
                'videos'     => $raw['channelVideos'] ?? null,
                'dt'         => $raw['channelDt'] ?? null,
                'local'      => $raw['channelCountry'] ?? null,
                'categ'      => $raw['channelCategory'] ?? null,
                'desc'       => $raw['channelDesc'] ?? null,
            ];

            $canalBD = $this->upsertCanal($ch, $buscaBD);
            #dump($canalBD);

            $videos = $this->getAllVideos($raw['channelId'], $raw['channelDt'], 100, 10, 1, $raw['channelVideos']);

            #$videos = $this->videos;
            #dump($videos);

            foreach ($videos as $vd) {
                $vd = [
                    'cod'      => $vd['videoId'],
                    'nome'     => $vd['videoTitle'] ?? null,
                    'desc'     => $vd['videoDesc'] ?? null,
                    'hashtags' => $vd['videoTags'] ?? [],
                    'comments' => $vd['videoCommentCount'] ?? null,
                    'likes'    => $vd['videoLikeCount'] ?? null,
                    'views'    => $vd['videoViewCount'] ?? null,
                    'duration' => $vd['videoDuration'] ?? null,
                    'lang'     => $vd['videoLang'] ?? null,
                    'dt'       => $vd['videoDt'] ?? null,
                    'categ_id' => $vd['videoCategId'] ?? null,

                    // novos campos de polarização:
                    'nlp1'      => $vd['nlp1'] ?? null,   // título
                    'nlp2'      => $vd['nlp2'] ?? null,   // descrição
                ];

                #dd($vd);
                $videoBD = $this->upsertVideo($vd, $canalBD, $buscaBD);
                #dump($videoBD);
            }



            $ordenados = collect($videos)
                ->filter(fn($c) => !empty($c['videoId']))
                ->sortBy(fn($c) => $c['videoDt'])
                ->values()
                ->toArray();


            $this->videos_dos_canais[$canalId] = $ordenados;
            $sessVideos[$canalId] = $ordenados;
        }

        #dd($sessVideos);

        // salva tudo na sessão (1 gravação só)
        Session::put('t2_videos', $sessVideos);

        $this->recalcularMedias();
        $arrayMaisPolarizados = $this->pickMaisPolariz($this->polarizMediaArray);
        $mais_polarizado_posit = $arrayMaisPolarizados['mais_polarizado_posit']['id'];
        $mais_polarizado_negat = $arrayMaisPolarizados['mais_polarizado_negat']['id'];
        $this->maisPolarizadoReal = $arrayMaisPolarizados['mais_polarizado']['id'];

        //  if ($bestPosId !== null) {
        //     $out['mais_polarizado_posit'] = ['id' => $bestPosId, 'score' => $bestPosVal];
        // }
        // if ($bestNegId !== null) {
        //     $out['mais_polarizado_negat'] = ['id' => $bestNegId, 'score' => $bestNegVal];
        // }
        // if ($bestAbsId !== null) {
        //     $out['mais_polarizado'] = ['id' => $bestAbsId, 'score' => $bestAbsScore];
        // }


        $this->acertou = ($this->maisPolarizadoReal && $this->maisPolarizado)
            ? $this->maisPolarizadoReal === $this->maisPolarizado
            : false;

        #dd($this->acertou);

        Session::put('t2_result', [
            'selecionados'      => $this->selecionados,
            'polariz_media'         => $this->polarizMediaArray,
            'mais_polarizado'       => $this->maisPolarizado,
            'mais_polarizado_real'  => $this->maisPolarizadoReal,
            'acertou'           => $this->acertou,
            'videos'       => $this->videos_dos_canais,
            'buscas'            => $this->buscas,

        ]);
    }




    public function salvarFeedback(): void
    {


        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback'          => $this->feedback,
            'acertou'           => Session::get('t2_result')['acertou'],

            'polariz_media'           => Session::get('t2_result')['polariz_media'],
            'mais_polarizado'           => Session::get('t2_result')['mais_polarizado'],
            'mais_polarizado_real'           => Session::get('t2_result')['mais_polarizado_real'],



        ];
        $status             = 1;
        $finished_at        = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));

        $msg = $t ? 'Obrigado! Sua tarefa #' . $tarefa_id . ' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #' . $tarefa_id;

        $this->clearSelecionados();
        $this->msg($msg, 'info');

        #dd($t);
    }





    /**
     * Retorna vídeos de um canal (mais recentes primeiro) já com nlp1 (título)
     * e nlp2 (descrição) calculados via setPolarization().
     */
    public function getAllVideos(
        string $channelId,
        ?string $channelCreatedAt = null,
        int $max = 100,
        int $maxPages = 10,
        int $page = 1,
        int $totalInformado = 0
    ) {
        static $acc = [];                // acumulador local (evita depender de $this->videos)
        static $nextToken = null;

        $key = env('YOUTUBE_API_KEY');
        $url = "https://www.googleapis.com/youtube/v3/search"
            . "?key={$key}"
            . "&channelId={$channelId}"
            . "&part=snippet"
            . "&order=date"
            . "&type=video"
            . "&maxResults=50";

        if ($page > 1 && $nextToken) {
            $url .= "&pageToken={$nextToken}";
        }

        $resp = Http::timeout(15)->get($url);
        if ($resp->failed()) {
            return $acc; // devolve o que já tiver
        }

        $json  = $resp->json();
        $items = $json['items'] ?? [];

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? [];
            $videoId = data_get($item, 'id.videoId');

            if (!$videoId) continue;

            $title = (string) ($snippet['title'] ?? '');
            $desc  = (string) ($snippet['description'] ?? '');

            // ⚠️ NLP aqui (com cache interno da própria setPolarization)
            $nlp1 = $this->setPolarization($title); // título
            $nlp2 = $this->setPolarization($desc);  // descrição

            $acc[] = [
                'videoId'      => $videoId,
                'videoTitle'   => $title,
                'videoDesc'    => $desc,
                'videoDt'      => $snippet['publishedAt'] ?? null,
                'channelId'    => $snippet['channelId'] ?? '',
                'channelTitle' => $snippet['channelTitle'] ?? '',
                'videoThumb'   => data_get($snippet, 'thumbnails.medium.url'),
                // novos campos:
                'nlp1'         => is_numeric($nlp1) ? (float)$nlp1 : null,
                'nlp2'         => is_numeric($nlp2) ? (float)$nlp2 : null,
            ];

            if (count($acc) >= $max) break;
        }

        // paginação
        $nextToken = $json['nextPageToken'] ?? null;
        $temMais   = $nextToken && (count($acc) < $max) && ($page < $maxPages);

        if ($temMais) {
            return $this->getAllVideos($channelId, $channelCreatedAt, $max, $maxPages, $page + 1, $totalInformado);
        }

        // ordenar por data (desc)
        usort($acc, fn($a, $b) => strtotime($b['videoDt'] ?? '1970-01-01') <=> strtotime($a['videoDt'] ?? '1970-01-01'));

        return array_slice($acc, 0, $max);
    }




    protected function recalcularMedias(): void
    {
        $medias = [];
        foreach ($this->videos_dos_canais as $chId => $lista) {
            $avg = collect($lista)
                ->map(function ($c) {
                    return $c['nlp1'] ?? null;
                })
                ->filter(fn($v) => is_numeric($v))
                ->avg();

            $medias[$chId] = is_numeric($avg) ? (float) $avg : null;
        }

        #dd($medias);
        $this->polarizMediaArray = $medias;
    }


    protected function pickMaisPolariz(array $scores): array
    {
        $EPS = 1e-9;

        $bestPosVal = -INF;
        $bestPosId = null;
        $bestNegVal =  INF;
        $bestNegId = null;

        $bestAbsVal = -INF;
        $bestAbsId = null;
        $bestAbsScore = null;

        foreach ($scores as $id => $v) {
            if (!is_numeric($v)) continue;
            $v = (float) $v;

            // campeão positivo (maior v)
            if ($v > $bestPosVal + $EPS) {
                $bestPosVal = $v;
                $bestPosId  = (string) $id;
            }

            // campeão negativo (menor v)
            if ($v < $bestNegVal - $EPS) {
                $bestNegVal = $v;
                $bestNegId  = (string) $id;
            }

            // campeão em |v| (polarização mais intensa)
            $abs = abs($v);
            if ($abs > $bestAbsVal + $EPS) {
                $bestAbsVal   = $abs;
                $bestAbsId    = (string) $id;
                $bestAbsScore = $v;  // mantém o sinal do campeão
            }
        }

        $out = [];

        if ($bestPosId !== null) {
            $out['mais_polarizado_posit'] = ['id' => $bestPosId, 'score' => $bestPosVal];
        }
        if ($bestNegId !== null) {
            $out['mais_polarizado_negat'] = ['id' => $bestNegId, 'score' => $bestNegVal];
        }
        if ($bestAbsId !== null) {
            $out['mais_polarizado'] = ['id' => $bestAbsId, 'score' => $bestAbsScore];
        }

        return $out;
    }





    // public function clearSelecionados(): void
    // {
    //     // 1) Resetar TODAS as props públicas para o default
    //     $this->reset();             // volta ao estado declarado na classe
    //     $this->resetErrorBag();     // (se usa validação)
    //     $this->resetValidation();   // (se usa validação)

    //     // 2) Limpar SESSIONS “da tela” por prefixo (não mexe em auth)
    //     $this->forgetSessionByPrefix($this->sessionPrefixes);

    //     // 3) Se usa paginação, zere a página atual
    //     if (method_exists($this, 'resetPage')) {
    //         $this->resetPage();
    //     }
    // }

    /**
     * Remove da sessão todas as chaves que começam com um dos prefixos informados.
     * Não toca em chaves de auth, CSRF etc.
     */
    // protected function forgetSessionByPrefix(array $prefixes): void
    // {
    //     $allKeys = array_keys(Session::all());

    //     $toForget = array_values(array_filter($allKeys, function ($key) use ($prefixes) {
    //         return Str::startsWith($key, $prefixes);
    //     }));

    //     if (!empty($toForget)) {
    //         Session::forget($toForget);
    //     }
    // }





    // public function pesquisarCanais(): void
    // {
    //     $this->query = $this->normalizeQuery($this->query);

    //     if (mb_strlen($this->query) < 2) {
    //         $this->msg('Digite pelo menos 2 caracteres para pesquisar.', 'warn');
    //         return;
    //     }

    //     $this->getCanais();
    // }

    // protected function persistSelecionados(): void
    // {

    //     // dump($this->selecionados);
    //     $this->selecionados = array_filter(
    //         $this->selecionados,
    //         static fn($value, $key) => is_string($key) && is_array($value) && !empty($value),
    //         ARRAY_FILTER_USE_BOTH
    //     );

    //     // dump($this->selecionados);

    //     if (empty($this->selecionados)) {
    //         Session::forget('t2_canais');
    //     } else {
    //         Session::put('t2_canais', $this->selecionados);
    //     }
    // }



    // Só libera a renderização do bloco
    public function avaliarCanais(): void
    {
        // Se quiser, dá pra exigir pelo menos 2 vídeos:
        if (count($this->selecionados) < 2)
            return;

        $this->mostrarAvaliacao = true;
        $this->videos_dos_canais  = [];
        $this->maisPolarizado = null;
        $this->polarizMediaArray = [];   // [videoId => float]

    }




    // public function add(string $canalId): void
    // {
    //     if (!isset($this->selecionados[$canalId])) {
    //         $tem = false;
    //         #dd($this->buscas);
    //         foreach ($this->buscas as $reg_canal) {
    //             if ($reg_canal['channelId'] == $canalId) {
    //                 $data = $reg_canal;
    //                 $tem = true;
    //                 break;
    //             }
    //         }
    //         if (!$tem) {
    //             $dadosCanal = $this->getCanaisDetailsByListCanaisIds([$canalId]);
    //             $data = $dadosCanal[$canalId] ?? null;   // pega só o registro do canal
    //             if (!$data) {
    //                 $this->msg('Não foi possível obter os detalhes do canal.', 'error');
    //                 return;
    //             }
    //             $data['q'] = '--';
    //         }
    //         if (is_array($data) && !empty($data)) {
    //             $this->selecionados[$canalId] = $data;
    //             #dd($this->selecionados);
    //             $this->persistSelecionados();
    //             $this->msg('Registro ' . $canalId . ' adicionado corretamente', 'success');
    //         }
    //     } else {
    //         $this->msg('Nao adicionado pois já consta', 'error');
    //     }
    //     #$this->reset('addInput'); // limpa o input na view

    // }



    // public function addCanalByInput(): void
    // {
    //     $input = trim($this->addInput);

    //     if ($input === '')
    //         return;

    //     $id = $this->parseCanalIdentifier($input);

    //     if ($id && !in_array($id, $this->selecionados, true)) {
    //         #dd($id);
    //         $this->add($id);
    //     } else {
    //         $this->msg('Canal com erro - nao adicionado', 'error');
    //     }
    //     $this->reset('addInput'); // limpa o input na view

    // }


    // protected function parseCanalIdentifier(string $input): bool|string
    // {
    //     // ID começa com UC + 22 chars
    //     if (preg_match('~^(UC[A-Za-z0-9_-]{22})$~', $input, $m)) {
    //         return $m[1];
    //     }
    //     // URL /channel/UC...
    //     if (preg_match('~channel/(UC[A-Za-z0-9_-]{22})~', $input, $m)) {
    //         return $m[1];
    //     }
    //     // @handle (ou URL com /@handle)
    //     if (preg_match('~@([A-Za-z0-9._-]{3,30})~', $input, $m)) {
    //         $handle = '@' . $m[1];
    //         $id = $this->pegaChannelIdViaHandle($handle);
    //         return $id;
    //         #dd($id);

    //     }
    //     // fallback: tentar achar pelo nome
    //     return false;
    // }


    // function pegaChannelIdViaHandle(string $handle)
    // {

    //     #dd($handle);
    //     $apiKey = env('YOUTUBE_API_KEY');
    //     $params = [
    //         'key'             => $apiKey,
    //         'part'            => 'id',
    //         'forHandle'       => $handle,
    //     ];

    //     $url = 'https://www.googleapis.com/youtube/v3/channels?' . http_build_query($params);
    //     $res = file_get_contents($url);
    //     if ($res) {
    //         $json = json_decode($res, true) ?: [];
    //         # dd($json);
    //         $id = $json['items'][0]['id'] ?? false;
    //         return $id;
    //     } else {
    //         $this->msg('Erro ao resolver @handle na API', 'error');
    //         return false;
    //     }
    // }







    public function getCanais(bool $forceRefresh = false): array
    {
        $q = trim((string) $this->query);
        if ($q === '') {
            return $this->buscas;
        }

        // cache por query (case-insensitive)
        $cacheKey = 'yt:search:channels:v1:' . md5(mb_strtolower($q));
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return $this->buscas = Cache::get($cacheKey);
        }

        $apiKey = env('YOUTUBE_API_KEY');

        // 1) Busca canais (máx 50)
        $url = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
            'key'        => $apiKey,
            'part'       => 'snippet',
            'q'          => $q,
            'type'       => 'channel',
            'maxResults' => 50,
        ]);
        Log::info('YT API:' . __CLASS__ . ' / ' . __FUNCTION__ . '()', ['url' => $url]);

        $resp  = file_get_contents($url);
        $json  = json_decode($resp ?? '[]', true);
        $items = collect($json['items'] ?? [])->values()->all();

        if (!$items) {
            Cache::put($cacheKey, [], now()->addDay());
            return $this->buscas = [];
        }

        // 2) Extrai os channelIds corretos (id.channelId)
        $channelIds = collect($items)->pluck('id.channelId')->filter()->unique()->values()->all();
        if (!$channelIds) {
            Cache::put($cacheKey, [], now()->addDay());
            return $this->buscas = [];
        }

        // 3) Hidrata detalhes dos canais
        $detailsById = $this->getCanaisDetailsByListCanaisIds($channelIds); // retorna array indexado por canalId

        // 4) preserva a ordem do search e adiciona 'q'
        $out = [];
        foreach ($items as $it) {
            $chId = $it['id']['channelId'] ?? null;
            if (!$chId || empty($detailsById[$chId])) continue;

            $row = $detailsById[$chId];
            $row['q'] = $q;           // anota a query usada
            $out[] = $row;
        }

        #dd($out);

        // 5) cache + retorno
        Cache::put($cacheKey, $out, now()->addDay());
        return $this->buscas = $out;
    }

    public function clearChannelsSearchCache(): void
    {
        $q = trim((string) $this->query);
        if ($q !== '') {
            Cache::forget('yt:search:channels:v1:' . md5(mb_strtolower($q)));
        }
    }



    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa2');
    }
}
