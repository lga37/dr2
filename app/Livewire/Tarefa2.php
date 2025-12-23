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

    public array $canais = [];

    public $buscas = [];
    public array $videos_dos_canais = [];
    public array $selecionados = [];
    public string $addInput = '';
    #public ?string $maisPolarizado = null;
    public array $polarizMediaArray = [];
    public ?string $maisPolarizadoReal = null;
    #public ?bool $acertou = null;
    public string $feedback = '';       // textarea
    public bool $mostrarAvaliacao = false;
    #public bool $mostrarFeedback = false;
    public array $arrayMaisPolarizados = [];

    // opcional: extraia isso pra um trait e reutilize nos dois componentes
    protected array $sessionPrefixes = ['t2_result', 't2_query', 't2_canais', 't2_buscas']; // ajuste como preferir


    public array $chart = [];
    public array $samples = [];          // [canal => [amostras]]
    public array $canalMedias = [];      // [canal => média -100..+100]
    public ?string $canalMaisPolar = null;

    
    function getTipoTarefa(): string
    {
        return 't2';
    }

    public function mount()
    {
        #$this->selecionados   = Session::get('t2_canais', []);
        #$this->query = Session::get('t2_query', '');
        #$this->avaliarCanaisGpt();
    }

  
    public function validarTarefa2()
    {

        #$this->selecionados = Session::get('t2_canais', $this->selecionados);
        $this->mostrarAvaliacao = true;


        if (count($this->selecionados) < 2)
            return;

        #$this->mostrarAvaliacao = true;
        $this->videos_dos_canais  = [];
        $this->maisPolarizado = null;
        $this->polarizMediaArray = [];   // [videoId => float]

        #Session::forget('t2_videos');
        $sessVideos = [];

        foreach ($this->selecionados as $canalId => $raw) {
            $q = $raw['q'] ?? '--';
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

            $videos = $this->getAllVideos($raw['channelId'], $raw['channelDt'], 100, 5, 1, $raw['channelVideos']);

            #dd($videos);

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

            $ordenados = collect($videos)->filter(fn($c) => !empty($c['videoId']))->sortBy(fn($c) => $c['videoDt'])->values()->toArray();

            $this->videos_dos_canais[$canalId] = $ordenados;
            $sessVideos[$canalId] = $ordenados;
        }

        #dd($sessVideos);


foreach ($this->videos_dos_canais as $ch => $vids) {
    $bad = collect($vids)->filter(fn($v) => ($v['channelId'] ?? null) !== $ch)->take(3)->values()->all();
    if ($bad) {
        logger()->warning("MISTURA DETECTADA em $ch", $bad);
    }
}

        $this->buildChartPolarizacao($this->selecionados, $this->videos_dos_canais);

        

        $this->recalcularMedias(); #$this->polarizMediaArray; so sai o id=>media
        #dump($this->polarizMediaArray);
        $arrayMaisPolarizados = $this->pickMaisPolariz($this->polarizMediaArray);
        $mais_polarizado_posit = $arrayMaisPolarizados['mais_polarizado_posit']['id'];
        $mais_polarizado_negat = $arrayMaisPolarizados['mais_polarizado_negat']['id'];
        $this->maisPolarizadoReal = $arrayMaisPolarizados['mais_polarizado']['id'];


        $this->dispatch('t2-chart-updated', chart: $this->chart);

        #dd($this->chart);

      
    }

 /** Constrói $this->chart, $this->samples, $this->canalMedias */
function buildChartPolarizacao(array $selecionados, array $todosVideos): void
{
    // 1) menor data (videoDt/dt/publishedAt) entre todos os canais
    $globalStart = null;
    foreach ($todosVideos as $vids) {
        foreach ($vids as $v) {
            $dt = $v['publishedAt'] ?? $v['videoDt'] ?? $v['dt'] ?? null;
            if (empty($dt)) {
                continue;
            }

            $d = Carbon::parse($dt)->startOfDay();
            $globalStart = $globalStart ? min($globalStart, $d) : $d;
        }
    }

    if (!$globalStart) {
        $globalStart = now()->startOfDay();
    }

    $globalMin = PHP_INT_MAX;
    $globalMax = PHP_INT_MIN;
    $series    = [];
    $medias    = [];

    foreach ($todosVideos as $channelId => $videos) {
        $titlePoints = [];
        $descPoints  = [];
        $sum         = 0;
        $n           = 0; // para média do canal

        $localMin = PHP_INT_MAX;
        $localMax = PHP_INT_MIN;

        # -------------- agora
        $sumTitle = 0; $nTitle = 0;
        $sumDesc  = 0; $nDesc  = 0;
        # -------------- agora

        foreach ($videos as $v) {
            $dt = $v['publishedAt'] ?? $v['videoDt'] ?? $v['dt'] ?? null;
            if (empty($dt)) {
                continue;
            }

            $dayIdx   = max(0, $globalStart->diffInDays(Carbon::parse($dt)->startOfDay()));
            $localMin = min($localMin, $dayIdx);
            $localMax = max($localMax, $dayIdx);

            // pega as polarizações (novo formato)
            $pTitle = $v['polar_title'] ?? $v['nlp1'] ?? null;
            $pDesc  = $v['polar_desc']  ?? $v['nlp2'] ?? null;

            // if (is_numeric($pTitle)) {
            //     $titlePoints[] = [
            //         'x'     => $dayIdx,
            //         'y'     => (float) $pTitle, // já -100..+100
            //         'label' => mb_substr($v['videoTitle'] ?? $v['nome'] ?? '', 0, 20),
            //     ];
            //     $sum += $pTitle;
            //     $n++;
            // }

            // if (is_numeric($pDesc)) {
            //     $descPoints[] = [
            //         'x'     => $dayIdx,
            //         'y'     => (float) $pDesc,
            //         'label' => mb_substr($v['videoDesc'] ?? $v['desc'] ?? '', 0, 20),
            //     ];
            //     $sum += $pDesc;
            //     $n++;
            // }

            if (is_numeric($pTitle)) {
                $titlePoints[] = ['x'=>$dayIdx,'y'=>(float)$pTitle,'label'=>mb_substr($v['videoTitle'] ?? $v['nome'] ?? '',0,20)];
                $sumTitle += (float)$pTitle;
                $nTitle++;
            }

            if (is_numeric($pDesc)) {
                $descPoints[]  = ['x'=>$dayIdx,'y'=>(float)$pDesc,'label'=>mb_substr($v['videoDesc'] ?? $v['desc'] ?? '',0,20)];
                $sumDesc += (float)$pDesc;
                $nDesc++;
            }


        }

        if ($localMin === PHP_INT_MAX) {
            $localMin = 0;
            $localMax = 0;
        }

        $globalMin = min($globalMin, $localMin);
        $globalMax = max($globalMax, $localMax);

      

        $avgTitle = $nTitle ? round($sumTitle / $nTitle, 2) : null;
        $avgDesc  = $nDesc  ? round($sumDesc  / $nDesc, 2) : null;

        $medias[$channelId] = $avgTitle;


        $series[$channelId] = [
        'title'        => $selecionados[$channelId]['channelTitle'] ?? $channelId,
        'points_title' => $titlePoints,
        'points_desc'  => $descPoints,
        'avg'          => $avgTitle,   // média = título (pra bater com o Blade)
        'avg_title'    => $avgTitle,
        'avg_desc'     => $avgDesc,
        'startDay'     => $localMin,
        'endDay'       => $localMax,
        ];


        // samples p/ tabela (se quiser manter)
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




    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback'          => $this->feedback,
            'polariz_media'           => $this->polarizMediaArray,
            'mais_polarizado_real'           => $this->maisPolarizadoReal,
        ];


        $status             = 1;
        $finished_at        = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));
        $msg = $t ? 'Obrigado! Sua tarefa #' . $tarefa_id . ' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #' . $tarefa_id;
        $this->clearSelecionados();
        $this->msg($msg, 'info');
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




    public function getAllVideos(
            string $channelId,
            ?string $channelCreatedAt = null,
            int $max = 100,
            int $maxPages = 5,
            int $page = 1,
            int $totalInformado = 0,
            array $acc = [],
            ?string $pageToken = null
        ) {
        $key = env('YOUTUBE_API_KEY');

        $url = "https://www.googleapis.com/youtube/v3/search"
            . "?key={$key}"
            . "&channelId={$channelId}"
            . "&part=snippet"
            . "&order=date"
            . "&type=video"
            . "&maxResults=50";

        if ($page > 1 && $pageToken) {
            $url .= "&pageToken={$pageToken}";
        }

        $resp = Http::timeout(15)->get($url);
        if ($resp->failed()) {
            return $acc;
        }

        $json  = $resp->json();
        $items = $json['items'] ?? [];

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? [];
            $videoId = data_get($item, 'id.videoId');
            if (!$videoId) continue;

            $title = (string) ($snippet['title'] ?? '');
            $desc  = (string) ($snippet['description'] ?? '');

            // setTox => 0..1? (você tá convertendo pra 0..100 aqui)
            $nlp1 = 100 * $this->setTox($title);
            $nlp2 = 100 * $this->setTox($desc);

            $acc[] = [
                'videoId'      => $videoId,
                'videoTitle'   => $title,
                'videoDesc'    => $desc,
                'videoDt'      => $snippet['publishedAt'] ?? null,
                'channelId'    => $snippet['channelId'] ?? '',
                'channelTitle' => $snippet['channelTitle'] ?? '',
                'videoThumb'   => data_get($snippet, 'thumbnails.medium.url'),
                'nlp1'         => is_numeric($nlp1) ? (float)$nlp1 : null,
                'nlp2'         => is_numeric($nlp2) ? (float)$nlp2 : null,
            ];

            if (count($acc) >= $max) break;
        }

        $nextToken = $json['nextPageToken'] ?? null;
        $temMais   = $nextToken && (count($acc) < $max) && ($page < $maxPages);

        if ($temMais) {
            return $this->getAllVideos(
                $channelId,
                $channelCreatedAt,
                $max,
                $maxPages,
                $page + 1,
                $totalInformado,
                $acc,
                $nextToken
            );
        }

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

        #dump($scores);

        $bestPosVal = -INF;
        $bestPosId = null;
        $bestNegVal =  INF;
        $bestNegId = null;

        $bestAbsVal = -INF;
        $bestAbsId = null;
        $bestAbsScore = null;

        foreach ($scores as $id => $v) {
            if (!is_numeric($v)) 
                continue;

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

        #dd($out);
        return $out;
    }


  


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



    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa2');
    }
}
