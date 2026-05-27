<?php

namespace App\Livewire;

use App\Models\Tarefa;
use App\Traits\Comum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Log;

class Tarefa4 extends Component
{
    use Comum;

    public array $canais = [];

    public $buscas = [];

    public array $videos_dos_canais = [];

    public array $selecionados = [];

    public array $deselected = [];

    public string $addInput = '';

    public array $checked = [];       // << id => true (checado = incluir)

    public string $feedback = '';       // textarea

    public bool $mostrarAvaliacao = true;

    public bool $mostrarFeedback = false;

    protected array $sessionPrefixes = ['t4_query', 't4_canais', 't4_checked', 't4_buscas', 't4_unchecked', 't4_videos_dos_canais']; // ajuste como preferir

    public array $unchecked = []; // ids que o usuário desmarcou

    public array $pmtTemaResult = [];

    // ################################
    public array $comentarios = [];

    public array $toxMediaArray = [];   // [videoId => float]

    public array $chart = [];

    public function getTipoTarefa(): string
    {
        return 't4';
    }

    public function mount()
    {
        $this->pmtTemaResult = Session::get('t4_result', []);
        $this->selecionados = Session::get('t4_videos_sel', []);
        $this->buscas = Session::get('t4_videos_buscas', []);

    }

    public function clearSelecionados(): void
    {
        $this->reset();             // volta ao estado declarado na classe

        Session::forget([
            't4_canais',
            't4_buscas',
            't4_checked',
            't4_unchecked',
            't4_videos_sel',
            't4_videos_buscas',
        ]);

        // =====================================================
        // SESSION
        // =====================================================

        Session::forget('t4_result');
        Session::forget('t4_videos_sel');
        Session::forget('t4_videos_buscas');

        // se tiver outras:
        Session::forget('t4_chart');
        Session::forget('t4_polarizacao');

        // =====================================================
        // PROPRIEDADES LIVEWIRE
        // =====================================================

        $this->buscas = [];
        $this->selecionados = [];
        $this->videos = [];
        $this->chart = [];
        $this->pmtTemaResult = [];
        $this->resultados = [];
        $this->polarizacao = [];
        $this->monetizacao = [];
        $this->comentarios = [];

        // =====================================================
        // FLAGS
        // =====================================================

        $this->loading = false;
        $this->erro = null;

        // =====================================================
        // FRONTEND
        // =====================================================

        $this->dispatch('t4-chart-clear');

        // opcional:

    }

    public function addTodos(): void
    {

        $marcados = collect($this->buscas)
            ->filter(fn ($row) => ! empty($row['videoId']) &&
                ($this->checked[$row['videoId']] ?? false)
            )
            ->keyBy('videoId')
            ->toArray();

        $this->selecionados = $marcados;

        Session::put('t4_videos_sel', $this->selecionados);
    }

    public function add(string $videoId): void
    {
        if (! isset($this->selecionados[$videoId])) {
            $tem = false;
            foreach ($this->buscas as $reg_video_canal) {
                // dd($reg_video_canal);
                if ($reg_video_canal['videoId'] == $videoId) {
                    $data = $reg_video_canal;
                    $tem = true;
                    break;
                }
            }
            if (! $tem) {
                $data = $this->hydrateSingleVideo($videoId);
            }
            if (is_array($data) && ! empty($data)) {
                $this->selecionados[$videoId] = $data;
                $this->persistSelecionados();
                $this->msg('Registro '.$videoId.' adicionado corretamente', 'success');
            }
        } else {
            $this->msg('Nao adicionado pois já consta', 'error');
        }
    }

    public function addVideoByInput(): void
    {
        $input = trim($this->addInput);
        if ($input === '') {
            return;
        }

        $id = $this->parseVideoId($input);

        if (preg_match('~(?:v=|youtu\.be/)([A-Za-z0-9_-]{11})~', $input, $m)) {
            $id = $m[1];
        } elseif (preg_match('~^[A-Za-z0-9_-]{11}$~', $input)) {
            $id = $input;
        }

        if ($id && ! in_array($id, $this->selecionados, true)) {
            $this->add($id);
        }

        $this->addInput = '';
    }

    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback' => $this->feedback,

        ];
        $status = 1;
        $finished_at = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));

        $msg = $t ? 'Obrigado! Sua tarefa #'.$tarefa_id.' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #'.$tarefa_id;

        $this->clearSelecionados();
        $this->msg($msg, 'info');

        // dd($t);
    }

    public function avaliarVideos(): void
    {

        $this->mostrarFeedback = true;

        $videos = $this->obterVideosMarcados(3);

        Log::info('T4 VIDEOS MARCADOS PARA ANALISE', [
            'query' => $this->query ?? null,
            'total' => count($videos),
        ]);

        if (empty($videos)) {
            $this->msg('Nenhum vídeo marcado para análise.', 'warn');

            return;
        }

        $query = $this->query ?? '[sem query]';

        $buscaBD = $this->upsertBusca($query);
        $persistidos = $this->persistirVideosECanais($videos, $buscaBD);

        $chart = $this->processarToxicidadeTemporal($videos);

        $monetizacao = $this->processarMonetizacaoPorCanal($persistidos);

        $polarizacao = $this->pmt_polarizacao_tema($query, $persistidos);

        Log::info('T4 DEPOIS POLARIZACAO', [
            'polarizacao' => $polarizacao,
        ]);

        // $resultado = $this->montarResultadoFinal(
        //     query: $query,
        //     videos: $persistidos,
        //     monetizacao: $monetizacao,
        //     polarizacao: $polarizacao,
        //     chart: $chart
        // );

        $resultado = $this->montarResultadoFinal(
            query: $query,
            videos: $persistidos,
            monetizacao: $monetizacao,
            polarizacao: $polarizacao,
            chart: $chart
        );

        /*
        |--------------------------------------------------------------------------
        | Garante que o Blade encontre a polarização em:
        | $overview['polarizacao']
        |--------------------------------------------------------------------------
        */
        $resultado['overview']['polarizacao'] = [
            'categoria' => $polarizacao['categoria'] ?? null,
            'polo_dominante' => $polarizacao['polo_dominante'] ?? $polarizacao['polo'] ?? null,
            'polarizacao_score' => $polarizacao['polarizacao_score'] ?? $polarizacao['score'] ?? null,
            'confianca' => $polarizacao['confianca'] ?? null,
            'justificativa' => $polarizacao['justificativa'] ?? null,
        ];

        $this->pmtTemaResult = $resultado;

        Session::put('t4_result', $resultado);

        $this->dispatch('t4-chart-updated', chart: $this->chart);

    }

    protected function obterVideosMarcados(int $limit = 4): array
    {
        return collect($this->buscas)
            ->shuffle()
            ->filter(fn ($v) => ! empty($v['videoId']) && ($this->checked[$v['videoId']] ?? false))
            ->take($limit)
            ->values()
            ->toArray();
    }

    protected function processarToxicidadeTemporal(array $videos): array
    {
        $this->montarGraficoComentarios($videos);

        return [
            'chart' => $this->chart,
            'comentarios' => $this->comentarios,
            'tox_media_por_video' => $this->toxMediaArray,
        ];
    }

    public function montarGraficoComentarios(array $videos)
    {

        // dd($videos);

        $globalStart = collect($videos)->min(fn ($v) => $v['published']);
        $globalMin = PHP_INT_MAX;
        $globalMax = PHP_INT_MIN;

        $series = [];
        foreach ($videos as $vid => $v) {
            // $count = (int) $v['commentCount'];
            // $upIso = $v['published'];
            // $vid = $v['videoId'];

            $count = (int) (
                $v['commentCount']
                ?? $v['videoCommentCount']
                ?? $v['comments']
                ?? 0
            );

            $upIso = $v['published']
                ?? $v['videoDt']
                ?? $v['publishedAt']
                ?? now()->toIso8601String();

            $vid = $v['videoId']
                ?? $v['cod']
                ?? null;

            if (! $vid) {
                continue;
            }

            $buck = $this->getComentariosSemToxT4($vid, $count, $upIso, 5);

            // dd($buck);

            $all = $this->flattenCommentsFromBuckets($buck);

            // dd($all);

            Log::info('T4 tox comments', [
                'videoId' => $vid,
                'commentCountInput' => $count,
                'commentsReturned' => count($all),
            ]);

            $this->comentarios[$vid] = $all;

            // $this->samples[$vid] = $this->sampleComments($all, 20);
            $avg = $this->toxMedia($all);
            $this->toxMediaArray[$vid] = $avg;

            $built = $this->buildPointsForVideoAbs($all, $upIso, $globalStart);

            $series[$vid] = [
                'points' => $built['points'],
                'avg' => $avg !== null ? round($avg * 100, 2) : null,
                'min' => $built['minDay'],
                'max' => $built['maxDay'],
                'startDay' => $built['videoStartDay'],
                'endDay' => $built['videoEndDay'],
                'title' => $v['videoTitle'] ?? $v['title'] ?? $vid,

            ];

            $globalMin = min($globalMin, $built['minDay']);
            $globalMax = max($globalMax, $built['maxDay']);
        }
        if ($globalMin === PHP_INT_MAX) {
            $globalMin = 0;
            $globalMax = 0;
        }

        // dd($series);

        $this->chart = [
            'globalStart' => $globalStart,
            'min' => $globalMin,
            'max' => $globalMax,
            'series' => $series,
        ];

    }

    protected function persistirVideosECanais(array $videos, $buscaBD): array
    {

        $persistidos = [];

        foreach ($videos as $raw) {
            $channelId = $raw['channelId'] ?? null;
            $videoId = $raw['videoId'] ?? null;

            if (! $channelId || ! $videoId) {
                continue;
            }

            $canalBD = $this->upsertCanal([
                'youtube_id' => $channelId,
                'nome' => $raw['channelTitle'] ?? null,
                'desc' => $raw['channelDesc'] ?? null,
                'inscritos' => $raw['channelSubs'] ?? null,
                'views' => $raw['channelViews'] ?? null,
                'videos' => $raw['channelVideos'] ?? null,
                'dt' => $raw['channelDt'] ?? null,
                'local' => $raw['channelCountry'] ?? null,
            ], $buscaBD);

            $videoBD = $this->upsertVideo([
                'cod' => $videoId,
                'nome' => $raw['videoTitle'] ?? null,
                'desc' => $raw['videoDesc'] ?? null,
                'hashtags' => $raw['videoTags'] ?? [],
                'comments' => $raw['commentCount'] ?? null,
                'likes' => $raw['likeCount'] ?? null,
                'views' => $raw['viewCount'] ?? null,
                'duration' => $raw['duration'] ?? null,
                'lang' => $raw['lang'] ?? null,
                'dt' => $raw['published'] ?? null,
                'categ_id' => $raw['videoCategId'] ?? null,
            ], $canalBD, $buscaBD);

            // $urls = $this->pmt_extract_external_urls($raw['videoDesc'] ?? '');

            $persistidos[] = [
                ...$raw,
                'canal_db_id' => $canalBD->id,
                'video_db_id' => $videoBD->id,
                // 'external_urls_count' => count($urls),
                // 'external_urls' => $urls,
            ];
        }

        return $persistidos;
    }

    protected function processarMonetizacaoPorCanal(array $videos): array
    {
        $porCanal = [];

        foreach ($videos as $v) {
            $channelId = $v['channelId'] ?? null;

            if (! $channelId) {
                continue;
            }

            if (! isset($porCanal[$channelId])) {
                $porCanal[$channelId] = [
                    'channelId' => $channelId,
                    'channelTitle' => $v['channelTitle'] ?? null,

                    // Uma única chamada por canal
                    'vidiq_monthly_avg_usd' => $this->pmt_get_vidiq_monthly_avg_usd($channelId),

                    'videos' => [],
                    'views' => 0,
                    'likes' => 0,
                    'comments' => 0,
                    'external_urls_count' => 0,
                    'external_urls' => [],
                ];
            }

            $videoId = $v['videoId'] ?? null;

            if ($videoId) {
                $porCanal[$channelId]['videos'][] = $videoId;
            }

            // Metadados do vídeo atual
            $viewsVideo = $this->getVideoViews($v);
            $likesVideo = $this->getVideoLikes($v);
            $commentsMetaVideo = $this->getVideoCommentsCount($v);

            Log::info('T4 SOMA VIDEO EM CANAL', [
                'channelId' => $channelId,
                'videoId' => $videoId,
            ]);

            $porCanal[$channelId]['views'] += $viewsVideo;
            $porCanal[$channelId]['likes'] += $likesVideo;
            $porCanal[$channelId]['comments'] += $commentsMetaVideo;

            // URLs externas: precisa verificar vídeo por vídeo
            $desc = $this->getVideoDescription($v);
            $urls = $this->pmt_extract_external_urls($desc);

            $porCanal[$channelId]['external_urls_count'] += count($urls);

            foreach ($urls as $url) {
                $porCanal[$channelId]['external_urls'][] = [
                    'videoId' => $videoId,
                    'url' => $url,
                ];

            }
        }

        return $porCanal;
    }

    protected function montarResultadoFinal(
        string $query,
        array $videos,
        array $monetizacao,
        array $polarizacao,
        array $chart
    ): array {
        $videosCount = count($videos);

        $channelIds = collect($videos)
            ->pluck('channelId')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $toxMedias = array_values(array_filter(
            $chart['tox_media_por_video'] ?? [],
            'is_numeric'
        ));

        $toxMedia = count($toxMedias)
            ? array_sum($toxMedias) / count($toxMedias)
            : null;

        $toxMax = count($toxMedias)
            ? max($toxMedias)
            : null;

        // $viewsTotal = collect($videos)->sum(fn ($v) => (int) ($v['viewCount'] ?? 0));
        // $likesTotal = collect($videos)->sum(fn ($v) => (int) ($v['likeCount'] ?? 0));
        // $commentsTotal = collect($videos)->sum(fn ($v) => (int) ($v['commentCount'] ?? 0));

        $viewsTotal = collect($monetizacao)->sum(fn ($c) => (int) ($c['views'] ?? 0));
        $likesTotal = collect($monetizacao)->sum(fn ($c) => (int) ($c['likes'] ?? 0));
        $commentsTotal = collect($monetizacao)->sum(fn ($c) => (int) ($c['comments'] ?? 0));

        $urlsTotal = collect($monetizacao)
            ->sum(fn ($c) => (int) ($c['external_urls_count'] ?? 0));

        $vidiqValores = collect($monetizacao)
            ->pluck('vidiq_monthly_avg_usd')
            ->filter(fn ($v) => is_numeric($v))
            ->values();

        $vidiqMedia = $vidiqValores->count()
            ? $vidiqValores->avg()
            : null;

        $vidiqMax = $vidiqValores->count()
            ? $vidiqValores->max()
            : null;

        $comentariosAnalisados = collect($chart['comentarios'] ?? [])
            ->sum(fn ($items) => is_array($items) ? count($items) : 0);

        return [
            'query' => $query,

            'overview' => [
                'videos' => $videosCount,
                'canais' => count($channelIds),

                'views_total' => $viewsTotal,
                'likes_total' => $likesTotal,

                'comentarios' => $comentariosAnalisados,
                'comentarios_analisados' => $comentariosAnalisados,

                'tox_media' => $toxMedia,
                'tox_max' => $toxMax,

                'urls_total' => $urlsTotal,
                'monet_media' => $vidiqMedia,
                'monet_max' => $vidiqMax,

                'polarizacao' => $polarizacao,
            ],

            'videos' => $videos,

            'canais' => array_values($monetizacao),

            'toxicidade' => [
                'chart' => $chart['chart'] ?? [],
                'comentarios' => $chart['comentarios'] ?? [],
                'tox_media_por_video' => $chart['tox_media_por_video'] ?? [],
            ],

            // 'monetizacao' => $monetizacao,

            // 'polarizacao' => $polarizacao,

            'polarizacao' => [
                'categoria' => $polarizacao['categoria'] ?? '-',

                'polo_dominante' => $polarizacao['polo_dominante']
                    ?? $polarizacao['polo_ideologico']
                    ?? $polarizacao['polo']
                    ?? '-',

                'polarizacao_score' => $polarizacao['polarizacao_score']
                    ?? $polarizacao['score']
                    ?? '-',

                'confianca' => $polarizacao['confianca'] ?? '-',

                'justificativa' => $polarizacao['justificativa'] ?? '',
            ],

            'meta' => [
                'gerado_em' => now()->toDateTimeString(),
                'limite_videos' => $videosCount,
                'fonte_monetizacao' => 'VidIQ por canal + URLs externas por vídeo',
                'observacao' => 'Monetização estimada por proxies; toxicidade e polarização inferidas por processamento externo.',
            ],
        ];
    }

    // #########################################################################

    protected function getVideoViews(array $v): int
    {
        return (int) (
            $v['viewCount']
            ?? $v['videoViewCount']
            ?? $v['views']
            ?? 0
        );
    }

    protected function getVideoLikes(array $v): int
    {
        return (int) (
            $v['likeCount']
            ?? $v['videoLikeCount']
            ?? $v['likes']
            ?? 0
        );
    }

    protected function getVideoCommentsCount(array $v): int
    {
        return (int) (
            $v['commentCount']
            ?? $v['videoCommentCount']
            ?? $v['comments']
            ?? 0
        );
    }

    protected function getVideoDescription(array $v): string
    {
        return (string) (
            $v['videoDesc']
            ?? $v['description']
            ?? $v['desc']
            ?? ''
        );
    }

    // ###############################

    protected function getComentariosSemToxT4(
        string $videoId,
        int $commentCount,
        string $uploadAtIso,
        int $perBucket = 100,
        bool $withTox = true,
        bool $forceRefresh = false): array
    {
        $videoId = trim($videoId);
        if ($videoId === '' || $commentCount < 0) {
            return [];
        }

        $apiKey = env('YOUTUBE_API_KEY');
        $baseCT = 'https://www.googleapis.com/youtube/v3/commentThreads';

        $uploadAt = Carbon::parse($uploadAtIso);
        $now = now();

        // 1) Quantos buckets/páginas (régua por contagem)
        $pages = 1;
        // if ($commentCount > 500 && $commentCount <= 2000)       $pages = 2;
        // elseif ($commentCount <= 5000)                          $pages = 3;
        // elseif ($commentCount <= 10000)                         $pages = 4;
        // elseif ($commentCount > 10000)                          $pages = 5;

        // vou so pegar 1 pag e ta bom demais - latencia

        // 3) Janelas temporais iguais (uploadAt..now), tamanho = $pages
        $windows = [];
        $totalSec = max(1, $uploadAt->diffInSeconds($now));
        for ($i = 0; $i < $pages; $i++) {
            $start = $uploadAt->copy()->addSeconds((int) floor($totalSec * ($i / $pages)));
            $end = $uploadAt->copy()->addSeconds((int) floor($totalSec * (($i + 1) / $pages)));
            $windows[] = ['after' => $start, 'before' => $end];
        }

        // 4) Coletar N páginas por relevância (≈100 por página)
        $order = 'relevance';
        $pageSize = 5;
        $nextToken = null;
        $seen = [];
        $col = [];

        // for ($p = 0; $p < $pages; $p++) {

        $params = [
            'key' => $apiKey,
            'part' => 'snippet',          // ou 'snippet,replies'
            'maxResults' => $pageSize,
            'videoId' => $videoId,           // <- i maiúsculo
            'textFormat' => 'plainText',
            'order' => $order,
        ];
        // if ($nextToken) {
        //     $params['pageToken'] = $nextToken;
        // }

        $url = $baseCT.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        Log::info('YT API (relevance)', ['url' => $url, 'videoId' => $videoId]);

        $resp = Http::timeout(20)->get($url);

        // dd($resp);

        if ($resp->failed()) {
            $body = $resp->json();
            Log::warning('YT API failed', ['status' => $resp->status(), 'body' => $body]);

            return []; // ou break; se quiser manter parciais
        }

        $data = $resp->json();
        if (isset($data['error'])) {
            Log::warning('YT API error', $data['error']);

            return [];
        }

        $items = $data['items'] ?? [];
        // if (! $items) {
        //     break;
        // }

        // dd($items);
        foreach ($items as $it) {
            $top = $it['snippet']['topLevelComment'] ?? [];
            $sn = $top['snippet'] ?? [];
            $cid = $top['id'] ?? ($it['id'] ?? null);
            if (! $cid || isset($seen[$cid])) {
                continue;
            }

            $seen[$cid] = true;

            $dtIso = $sn['publishedAt'] ?? null;
            if (! $dtIso) {
                continue;
            }

            $txt = (string) ($sn['textDisplay'] ?? '');
            $plain = strip_tags($txt);

            $col[] = [
                'cod' => $cid,
                'username' => $sn['authorDisplayName'] ?? null,
                'texto' => $plain,
                'likes' => (int) ($sn['likeCount'] ?? 0),
                'dt' => $dtIso,
                'tox' => $withTox ? $this->setTox($plain) : null,
            ];
        }

        // $nextToken = $data['nextPageToken'] ?? null;
        // if (! $nextToken) {
        //     break;
        // }

        // } do for

        // 5) Particionar por janela e ordenar cronologicamente dentro do bucket
        $bucketRows = array_fill(0, $pages, []);

        // dd($col);

        foreach ($col as $row) {
            $dt = Carbon::parse($row['dt']);
            $idx = null;
            foreach ($windows as $i => $w) {
                if ($dt->gte($w['after']) && $dt->lt($w['before'])) {
                    $idx = $i;
                    break;
                }
            }
            if ($idx === null) {
                $idx = $pages - 1;
            } // bordas vão pro último
            $bucketRows[$idx][] = $row;
        }

        $out = [];
        for ($i = 0; $i < $pages; $i++) {
            // ordena por tempo asc
            usort($bucketRows[$i], fn ($a, $b) => strcmp($a['dt'] ?? '', $b['dt'] ?? ''));
            // garante até $perBucket por janela (se vier menos que 100, tudo bem)
            $items = array_slice($bucketRows[$i], 0, $perBucket);

            $out[] = [
                'bucket' => $i + 1,
                'publishedAfter' => $windows[$i]['after']->toIso8601String(),
                'publishedBefore' => $windows[$i]['before']->toIso8601String(),
                'items' => array_values($items),
            ];
        }

        return $out;
    }

    /**
     * Pontos para o gráfico com X absoluto:
     * - x = dias desde $globalStart (inteiro)
     * - y = tox (%) 0..100
     * - label = 15 primeiros chars
     * Também retorna:
     * - videoStartDay: dia do upload do vídeo (desde o globalStart)
     * - videoEndDay:   último dia com comentário (ou today), desde o globalStart
     */
    protected function buildPointsForVideoAbs(array $comments, string $uploadAtIso, string $globalStartIso): array
    {
        $globalStart = Carbon::parse($globalStartIso)->startOfDay();
        $upload = Carbon::parse($uploadAtIso)->startOfDay();

        $pts = [];
        $minDay = PHP_INT_MAX;
        $maxDay = PHP_INT_MIN;

        $videoStartDay = max(0, $globalStart->diffInDays($upload));
        $lastDate = $upload;

        foreach ($comments as $c) {
            $tox = $c['tox'] ?? null;
            $dt = $c['dt'] ?? null;
            if (! is_numeric($tox) || ! $dt) {
                continue;
            }

            $d = Carbon::parse($dt)->startOfDay();
            $day = max(0, $globalStart->diffInDays($d));

            $minDay = min($minDay, $day);
            $maxDay = max($maxDay, $day);
            if ($d->gt($lastDate)) {
                $lastDate = $d;
            }

            $plain = trim((string) ($c['texto'] ?? ''));

            $pts[] = [
                'x' => $day,
                'y' => round(((float) $tox) * 100, 2),
                'label' => mb_strimwidth($plain, 0, 120, '…'), // para tooltip (com corte elegante)
                'full' => $plain,                               // opcional: texto inteiro
            ];

        }

        if ($minDay === PHP_INT_MAX) {
            $minDay = $videoStartDay;
            $maxDay = $videoStartDay;
        }
        $videoEndDay = max($videoStartDay, $globalStart->diffInDays($lastDate));

        return [
            'points' => $pts,
            'minDay' => $minDay,
            'maxDay' => $maxDay,
            'videoStartDay' => $videoStartDay,
            'videoEndDay' => $videoEndDay,
        ];
    }

    /** Junta todos os itens dos buckets de um vídeo. */
    protected function flattenCommentsFromBuckets(array $bucketed): array
    {
        $all = [];
        foreach ($bucketed as $b) {
            foreach (($b['items'] ?? []) as $it) {
                $all[] = $it;
            }
        }

        return $all;
    }

    /** Média de toxicidade (0..1) de um conjunto de comentários. */
    protected function toxMedia(array $comments): ?float
    {
        $vals = array_values(array_filter(
            array_map(fn ($c) => $c['tox'] ?? null, $comments),
            'is_numeric'
        ));
        if (! $vals) {
            return null;
        }

        return array_sum($vals) / count($vals);
    }

    public function hydrateVideosFromSearchResults(array $searchItems): array
    {
        // $searchItems são os items do search? (id.videoId + snippet.*)
        // Extraia ids de vídeo E de canal:
        $videoIds = collect($searchItems)->pluck('id.videoId')->filter()->values()->all();
        $channelIds = collect($searchItems)->pluck('snippet.channelId')->filter()->unique()->values()->all();

        // 1) detalhes dos vídeos
        $videos = $this->getVideoDetailsByListVideoIds($videoIds);
        $byVid = collect($videos)->keyBy('videoId');

        // 2) detalhes dos canais
        $canais = $this->getCanaisDetailsByListCanaisIds($channelIds);

        // 3) monta retorno “hidratado”, preservando a ordem da busca:
        $out = [];
        foreach ($searchItems as $it) {
            $vid = $it['id']['videoId'] ?? null;
            if (! $vid) {
                continue;
            }

            $v = $byVid[$vid] ?? null;
            if (! $v) {
                continue;
            }

            $chId = $v['channelId'] ?? null;
            $ch = $chId ? ($canais[$chId] ?? null) : null;

            // merge “plano” (mantém suas chaves padrão)
            $out[] = array_merge($v, $ch ?? []);
        }

        return $out; // cada item já com video* + channel*
    }

    // ##############################################################

    public function pmt_polarizacao_tema(string $query, array $videos): array
    {
        $payload = collect($videos)
            ->take(30)
            ->map(fn ($v) => [
                'titulo' => $v['videoTitle'] ?? '',
                'descricao' => mb_substr($v['videoDesc'] ?? '', 0, 700),
                'tags' => $v['videoTags'] ?? [],
            ])
            ->values()
            ->all();

        $prompt = <<<PROMPT
        Você é um classificador acadêmico de polarização no YouTube.

        Analise o conjunto de vídeos retornado para a query: "{$query}".

        Considere como polarização:
        - presença de conflito político, ideológico, religioso, moral ou cultural;
        - linguagem de oposição entre grupos;
        - enquadramento nós versus eles;
        - associação explícita a campos políticos, identitários ou morais;
        - potencial de disputa discursiva.

        Atribua polarizacao_score:
        0.0 = sem polarização aparente;
        0.25 = baixa polarização;
        0.50 = polarização moderada;
        0.75 = alta polarização;
        1.0 = polarização extrema ou explicitamente conflitiva.

        Classifique:
        - categoria: politica, religiao, ciencia, saude, economia, tecnologia, entretenimento, educacao, outro
        - polo_dominante: esquerda, direita, centro, misto, indefinido
        - polarizacao_score: número de 0 a 1
        - confianca: número de 0 a 1
        - justificativa: breve explicação

        Retorne apenas JSON válido:
        {
        "categoria": "...",
        "polo_dominante": "...",
        "polarizacao_score": 0.0,
        "confianca": 0.0,
        "justificativa": "..."
        }
        
        PROMPT;

        try {
            $res = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(40)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'temperature' => 0.1,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Responda somente JSON válido.'],
                        [
                            'role' => 'user',
                            'content' => $prompt."\n\nDADOS:\n".json_encode($payload, JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ]);

            $content = trim($res->json('choices.0.message.content') ?? '');
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/```$/', '', $content);

            $json = json_decode($content, true);

            Log::info('T4 POLARIZACAO OPENAI RAW', [
                'status' => $res->status(),
                'body' => $res->body(),
                'content' => $content ?? null,
            ]);

            return is_array($json) ? $json : [
                'categoria' => 'outro',
                'polo_dominante' => 'indefinido',
                'polarizacao_score' => 0,
                'confianca' => 0,
                'justificativa' => 'Falha ao interpretar resposta da LLM.',
            ];

        } catch (\Throwable $e) {
            return [
                'categoria' => 'outro',
                'polo_dominante' => 'indefinido',
                'polarizacao_score' => 0,
                'confianca' => 0,
                'justificativa' => 'Erro na classificação: '.$e->getMessage(),
            ];
        }
    }

    #[Layout('layouts/app')]
    public function render()
    {

        return view('livewire.tarefa4');
    }
}
