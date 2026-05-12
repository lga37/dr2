<?php

namespace App\Livewire;

use App\Models\Tarefa;
use App\Traits\Comum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Log;

class Tarefa1 extends Component
{
    use Comum;

    public $buscas = []; // aqui entra os resultados da busca query

    public array $comentarios = [];

    public array $selecionados = [];

    public string $addInput = '';

    public array $toxMediaArray = [];   // [videoId => float]

    public ?string $maisToxicoReal = null; // videoId com maior média (ou null em empate)

    public string $feedback = '';       // textarea

    public bool $mostrarAvaliacao = false;   // controla a renderização do bloco

    public bool $mostrarFeedback = false;

    public array $chart = [];

    public array $samples = [];

    public function mount() {}

    public function getTipoTarefa(): string
    {
        return 't1';
    }

    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();

        $dados = [
            'feedback' => $this->feedback,
            'tox_media' => Session::get('t1_result')['tox_media'],
            'mais_toxico_real' => Session::get('t1_result')['mais_toxico_real'],

        ];
        $status = 1;
        $finished_at = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));

        $msg = $t ? 'Obrigado! Sua tarefa #'.$tarefa_id.' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #'.$tarefa_id;

        $this->clearSelecionados();
        $this->msg($msg, 'info');

        // dd($t);
    }

    public function clearSelecionados(): void
    {
        $this->reset();             // volta ao estado declarado na classe

        // limpa caches de tela
        Session::forget([
            't1_result',
            'sel_videos',
            't1_comentarios',
            'tarefa1_mais_toxico',
            't1_query',            // aproveita e zera a query salva
            // 'polarizacoes',

        ]);

        // $this->dispatch('$refresh');
    }

    public $polarizacoes = [];

    public function avaliarVideos(): void
    {

        // Se quiser, dá pra exigir pelo menos 2 vídeos:
        if (count($this->selecionados) < 2) {
            return;
        }

        // dd($this->selecionados);

        $this->selecionados = Session::get('sel_videos', $this->selecionados);
        $this->comentarios = [];
        // $this->mostrarFeedback = true;
        $this->mostrarAvaliacao = true;

        Session::forget('t1_comentarios');
        $sessComentarios = [];

        $this->montarGraficoComentarios($this->selecionados);

        foreach ($this->selecionados as $videoId => $raw) {
            $q = $raw['busca'] ?? '[erro]';
            $buscaBD = $this->upsertBusca($q);
            // dump($buscaBD);

            // dd($raw);

            $ch = [
                'youtube_id' => $raw['channelId'],
                'nome' => $raw['channelTitle'] ?? null,
                'keywords' => $raw['channelKeywords'] ?? [],
                'handle' => $raw['channelHandle'] ?? null,
                'inscritos' => $raw['channelSubs'] ?? null,
                'views' => $raw['channelViews'] ?? null,
                'videos' => $raw['channelVideos'] ?? null,
                'dt' => $raw['channelDt'] ?? null,
                'local' => $raw['channelCountry'] ?? null,
                // 'categ'      => $raw['channelCategory'] ?? null,
                'desc' => $raw['channelDesc'] ?? null,
            ];

            $canalBD = $this->upsertCanal($ch, $buscaBD);
            // dump($canalBD);

            $vd = [
                'cod' => $raw['videoId'],
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
            ];

            // dump($vd);
            $videoBD = $this->upsertVideo($vd, $canalBD, $buscaBD);

            $comentarios = $this->comentarios[$videoId] ?? [];

            // ordene/saneie em cima do array de COMENTÁRIOS (não do mapa global)
            $ordenados = collect($comentarios)
                ->filter(fn ($c) => ! empty($c['cod']) && ! empty($c['dt']) && is_numeric($c['tox'] ?? null))
                ->sortBy('dt')
                ->values()
                ->toArray();

            $this->comentarios[$videoId] = $ordenados;
            $sessComentarios[$videoId] = $ordenados;

            foreach ($ordenados as $comm) {
                if (! isset($comm['cod'])) {
                    continue;
                }

                $commBD = $this->upsertComentario($comm, $videoBD);
            }
        }
        Session::put('t1_comentarios', $sessComentarios);
        $this->recalcularMedias();

        $this->maisToxicoReal = $this->pickMaisToxico($this->toxMediaArray);

        $polarizacoes = [];

        foreach ($this->selecionados as $videoId => $raw) {

            $comentariosVideo = $this->comentarios[$videoId] ?? [];

            $transcript = $this->pmt_get_transcript($videoId);

            $polarizacoes[$videoId] = $this->pmt_polarizacao_video(
                video: [
                    'cod' => $raw['videoId'] ?? $videoId,
                    'nome' => $raw['videoTitle'] ?? null,
                    'desc' => $raw['videoDesc'] ?? null,
                    'dt' => $raw['published'] ?? null,
                    'views' => $raw['viewCount'] ?? null,
                    'likes' => $raw['likeCount'] ?? null,
                    'comments' => $raw['commentCount'] ?? null,
                ],
                channel: [
                    'youtube_id' => $raw['channelId'] ?? null,
                    'nome' => $raw['channelTitle'] ?? null,
                    'desc' => $raw['channelDesc'] ?? null,
                ],
                comments: array_slice($comentariosVideo, 0, 20),
                transcript: $transcript
            );

            $polarizacoes[$videoId]['transcript_words'] = $transcript
                ? str_word_count(strip_tags($transcript))
                : 0;
        }

        $this->polarizacoes = $polarizacoes;

        Session::put('t1_result', [
            'selecionados' => $this->selecionados,
            'tox_media' => $this->toxMediaArray,
            // 'mais_toxico'       => $this->maisToxico,
            'mais_toxico_real' => $this->maisToxicoReal,
            // 'acertou'           => $this->acertou,
            'comentarios' => $this->comentarios,
            'buscas' => $this->buscas,
            // 'polarizacoes'      => $polarizacoes,

        ]);

        $this->dispatch('t1-chart-updated', chart: $this->chart);
        // dump($this->chart);
    }

    public function montarGraficoComentarios(array $videos)
    {

        // dd('hh');

        $globalStart = collect($videos)->min(fn ($v) => $v['published']);
        $globalMin = PHP_INT_MAX;
        $globalMax = PHP_INT_MIN;

        $series = [];
        foreach ($videos as $vid => $v) {
            $count = (int) $v['commentCount'];
            $upIso = $v['published'];

            $buck = $this->getCommentsByBucketsRelevance($vid, $count, $upIso);

            // dd($buck);

            $all = $this->flattenCommentsFromBuckets($buck);

            $this->comentarios[$vid] = $all;

            $this->samples[$vid] = $this->sampleComments($all, 20);
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

    protected function getCommentsByBucketsRelevance(
        string $videoId, int $commentCount, string $uploadAtIso, int $perBucket = 100,
        bool $withTox = true, bool $forceRefresh = false): array {
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
        $pageSize = 100;
        $nextToken = null;
        $seen = [];
        $col = [];

        for ($p = 0; $p < $pages; $p++) {

            $params = [
                'key' => $apiKey,
                'part' => 'snippet',          // ou 'snippet,replies'
                'maxResults' => $pageSize,
                'videoId' => $videoId,           // <- i maiúsculo
                'textFormat' => 'plainText',
                'order' => $order,
            ];
            if ($nextToken) {
                $params['pageToken'] = $nextToken;
            }

            $url = $baseCT.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);

            Log::info('YT API (relevance)', ['page' => $p + 1, 'url' => $url, 'videoId' => $videoId]);

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
            if (! $items) {
                break;
            }

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

            $nextToken = $data['nextPageToken'] ?? null;
            if (! $nextToken) {
                break;
            }
        }

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

    /** Amostra N comentários aleatórios e estáveis (embaralha e pega N). */
    protected function sampleComments(array $comments, int $n = 20): array
    {
        if (count($comments) <= $n) {
            return $comments;
        }
        $idx = array_rand($comments, $n);
        if (! is_array($idx)) {
            $idx = [$idx];
        }

        return array_values(array_intersect_key($comments, array_flip($idx)));
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

    // ############################################################################
    protected function recalcularMedias(): void
    {
        $medias = [];
        foreach ($this->comentarios as $vid => $lista) {
            // aceita chave 'tox' ou 'toxicity', dependendo de onde veio
            $avg = collect($lista)
                ->map(function ($c) {
                    return $c['tox'] ?? null;
                })
                ->filter(fn ($v) => is_numeric($v))
                ->avg();

            $medias[$vid] = is_numeric($avg) ? (float) $avg : null;
        }
        $this->toxMediaArray = $medias;
    }

    protected function pickMaisToxico(array $medias): ?string
    {
        $valid = array_filter($medias, static fn ($v) => is_numeric($v));
        if (! $valid) {
            return null;
        }

        $max = max($valid);
        $EPS = 1e-9;

        foreach ($medias as $id => $v) {
            if (is_numeric($v) && abs($v - $max) <= $EPS) {
                return $id;
            }
        }

        // sem vencedor claro (não deveria acontecer)
        return null;
    }

    public function add(string $videoId): void
    {
        if (! isset($this->selecionados[$videoId])) {
            $tem = false;
            foreach ($this->buscas as $reg_video_canal) {
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

    // se quiser um helper para invalidar:
    public function clearSearchCache(): void
    {
        $q = trim((string) $this->query);
        if ($q !== '') {
            Cache::forget('yt:search:v2:'.md5(mb_strtolower($q)));
        }
    }

    // precisa pra qd se adiciona single video
    public function hydrateSingleVideo(string $videoId): ?array
    {
        $det = $this->getVideoDetailsByListVideoIds([$videoId]);
        if (! $det) {
            return null;
        }

        $v = $det[0];
        $chId = $v['channelId'] ?? null;

        if ($chId) {
            $canais = $this->getCanaisDetailsByListCanaisIds([$chId]);
            $ch = $canais[$chId] ?? null;
            if ($ch) {
                return array_merge($v, $ch);
            }
        }

        return $v;
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

    #[Layout('layouts/app')]
    public function render()
    {
        return view('livewire.tarefa1');
    }
}
