<?php

namespace App\Livewire;

use App\Models\Tarefa;
use App\Traits\Comum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Tarefa2 extends Component
{
    use Comum;

    public array $canais = [];

    public $buscas = [];

    public array $videos_dos_canais = [];

    public array $selecionados = [];

    public string $addInput = '';

    // public ?string $maisPolarizado = null;
    public array $polarizMediaArray = [];

    public ?string $maisPolarizadoReal = null;

    // public ?bool $acertou = null;
    public string $feedback = '';       // textarea

    public bool $mostrarAvaliacao = false;

    // public bool $mostrarFeedback = false;
    public array $arrayMaisPolarizados = [];

    // opcional: extraia isso pra um trait e reutilize nos dois componentes
    protected array $sessionPrefixes = ['t2_result', 't2_query', 't2_canais', 't2_buscas']; // ajuste como preferir

    public array $chart = [];

    public array $samples = [];          // [canal => [amostras]]

    public array $canalMedias = [];      // [canal => média -100..+100]

    public ?string $canalMaisPolar = null;

    public function getTipoTarefa(): string
    {
        return 't2';
    }

    public function mount() {}

    public function validarTarefa2()
    {

        $this->selecionados = Session::get('t2_canais', $this->selecionados);
        $this->mostrarAvaliacao = true;

        if (count($this->selecionados) < 2) {
            return;
        }

        // Session::forget('t2_videos');
        $sessVideos = [];

        $this->avaliarMonetizacaoToxicidade();

    }

    public array $mtResult = [];

    public function avaliarMonetizacaoToxicidade()
    {
        $resultado = [];

        $cores = ['green', 'red', 'blue', 'purple', 'orange', 'pink', 'cyan', 'slate'];

        $maxVideos = 3;
        $maxComentariosPorVideo = 4;
        $qtdBuckets = 5;

        $canais = $this->selecionados ?? [];

        \Log::info('[PMT] INICIO avaliarMonetizacaoToxicidade', [
            'canais' => count($canais),
        ]);

        foreach ($canais as $idx => $channel) {

            $channelId = $channel['channelId']
                ?? $channel['youtube_id']
                ?? $channel['id']
                ?? null;

            if (! $channelId) {
                continue;
            }

            $channelCreatedAt = $channel['channelCreatedAt']
                ?? $channel['channelDt']
                ?? $channel['publishedAt']
                ?? $channel['createdAt']
                ?? $channel['dt']
                ?? $channel['created_at']
                ?? null;

            \Log::info('[PMT] CANAL inicio', [
                'idx' => $idx,
                'channelId' => $channelId,
                'channelCreatedAt' => $channelCreatedAt,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 1. Vídeos do canal
            |--------------------------------------------------------------------------
            */
            $videosOrdenados = $this->getAllVideos($channelId, $maxVideos);

            usort($videosOrdenados, function ($a, $b) {
                return strtotime($b['videoDt'] ?? $b['publishedAt'] ?? '1970-01-01')
                    <=>
                    strtotime($a['videoDt'] ?? $a['publishedAt'] ?? '1970-01-01');
            });

            \Log::info('[PMT] VIDEOS coletados', [
                'channelId' => $channelId,
                'qtd' => count($videosOrdenados),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2. Fallback se não vier data do canal
            |--------------------------------------------------------------------------
            */
            if (! $channelCreatedAt && ! empty($videosOrdenados)) {
                $datas = collect($videosOrdenados)
                    ->map(fn ($v) => $v['videoDt'] ?? $v['publishedAt'] ?? null)
                    ->filter()
                    ->sort()
                    ->values();

                $channelCreatedAt = $datas->first();
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Buckets temporais
            |--------------------------------------------------------------------------
            */
            $buckets = $this->pmt_bucket_periods($channelCreatedAt, $qtdBuckets);

            $bucketResults = [];

            foreach ($buckets as $bucket) {
                $bucketResults[$bucket['idx']] = [
                    'idx' => $bucket['idx'],
                    'label' => $bucket['label'],
                    'after' => $bucket['after'],
                    'before' => $bucket['before'],
                    'videos_count' => 0,
                    'comments_count' => 0,
                    'tox_scores' => [],
                    'comments' => [],
                    'external_urls_count' => 0,
                ];
            }

            \Log::info('[PMT] BUCKETS criados', [
                'channelId' => $channelId,
                'qtd' => count($bucketResults),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Monetização ON — função existente no comum
            |--------------------------------------------------------------------------
            */
            $vidiqMonthlyAvgUsd = $this->pmt_get_vidiq_monthly_avg_usd($channelId);

            /*
            |--------------------------------------------------------------------------
            | 5. Monetização OFF — URLs externas das descrições
            |--------------------------------------------------------------------------
            */
            $urlsMonetizacao = [];

            foreach ($videosOrdenados as $video) {

                $videoDt = $video['videoDt'] ?? $video['publishedAt'] ?? null;
                $desc = $video['videoDesc'] ?? $video['description'] ?? '';

                $urls = $this->pmt_extract_external_urls($desc);

                if (! empty($urls)) {
                    $urlsMonetizacao = array_merge($urlsMonetizacao, $urls);
                }

                foreach ($bucketResults as &$bucket) {
                    if ($videoDt && $videoDt >= $bucket['after'] && $videoDt <= $bucket['before']) {
                        $bucket['external_urls_count'] += count($urls);
                        break;
                    }
                }
                unset($bucket);
            }

            $urlsMonetizacao = array_values(array_unique($urlsMonetizacao));

            /*
            |--------------------------------------------------------------------------
            | 6. Vídeos -> comentários -> toxicidade -> buckets
            |--------------------------------------------------------------------------
            */
            $toxTodas = [];

            foreach ($videosOrdenados as $videoIndex => $video) {

                $videoId = $video['videoId'] ?? null;
                $videoDt = $video['videoDt'] ?? $video['publishedAt'] ?? null;

                if (! $videoId) {
                    continue;
                }

                foreach ($bucketResults as &$bucket) {
                    if ($videoDt && $videoDt >= $bucket['after'] && $videoDt <= $bucket['before']) {
                        $bucket['videos_count']++;
                        break;
                    }
                }
                unset($bucket);

                $comentarios = $this->getComentariosVideo($videoId, $maxComentariosPorVideo);

                \Log::info('[PMT] COMENTARIOS coletados', [
                    'videoId' => $videoId,
                    'qtd' => count($comentarios),
                ]);

                foreach ($comentarios as $commentIndex => $comentario) {

                    $texto = $comentario['text']
                        ?? $comentario['commentText']
                        ?? $comentario['texto']
                        ?? '';

                    $commentDt = $comentario['publishedAt']
                        ?? $comentario['commentDt']
                        ?? $comentario['dt']
                        ?? null;

                    if (! $texto || ! $commentDt) {
                        continue;
                    }

                    $tox = $this->setTox($texto);

                    if (! is_numeric($tox)) {
                        continue;
                    }

                    $tox = round((float) $tox, 4);
                    $toxTodas[] = $tox;

                    foreach ($bucketResults as &$bucket) {
                        if ($commentDt >= $bucket['after'] && $commentDt <= $bucket['before']) {

                            $bucket['comments_count']++;
                            $bucket['tox_scores'][] = $tox;

                            if (count($bucket['comments']) < 3) {
                                $bucket['comments'][] = [
                                    'videoId' => $videoId,
                                    'videoTitle' => $video['videoTitle'] ?? '',
                                    'texto' => $texto,
                                    'text' => $texto,
                                    'publishedAt' => $commentDt,
                                    'tox' => $tox,
                                ];
                            }

                            break;
                        }
                    }
                    unset($bucket);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 7. Fecha buckets no formato do Blade
            |--------------------------------------------------------------------------
            */
            foreach ($bucketResults as &$bucket) {

                $scores = $bucket['tox_scores'];

                $bucket['analysis'] = [
                    'videos_count' => $bucket['videos_count'],

                    'toxicity' => [
                        'n' => count($scores),
                        'media' => count($scores)
                            ? round(array_sum($scores) / count($scores), 4)
                            : null,
                        'max' => count($scores)
                            ? round(max($scores), 4)
                            : null,
                        'alta_taxa' => count($scores)
                            ? round(count(array_filter($scores, fn ($v) => $v >= 0.7)) / count($scores), 4)
                            : null,
                    ],

                    'monetizacao_off_platform' => [
                        'urls_count' => $bucket['external_urls_count'],
                        'urls_media_por_video' => $bucket['videos_count']
                            ? round($bucket['external_urls_count'] / $bucket['videos_count'], 4)
                            : 0,
                    ],

                    'comentarios_sample' => $bucket['comments'] ?? [],
                ];

                unset(
                    $bucket['tox_scores'],
                    $bucket['comments'],
                    $bucket['external_urls_count']
                );
            }
            unset($bucket);

            $bucketResults = array_values($bucketResults);

            /*
            |--------------------------------------------------------------------------
            | 8. Resultado final do canal
            |--------------------------------------------------------------------------
            */
            $resultado[$channelId] = [
                'cor' => $cores[$idx] ?? 'slate',

                'channel' => $channel,

                'total_videos_coletados' => count($videosOrdenados),

                'tox_canal' => [
                    'n' => count($toxTodas),
                    'media' => count($toxTodas)
                        ? round(array_sum($toxTodas) / count($toxTodas), 4)
                        : null,
                    'max' => count($toxTodas)
                        ? round(max($toxTodas), 4)
                        : null,
                ],

                'buckets' => $bucketResults,

                'monetizacao_canal' => [
                    'vidiq_monthly_avg_usd' => is_numeric($vidiqMonthlyAvgUsd)
                        ? (float) $vidiqMonthlyAvgUsd
                        : null,

                    'external_urls_count' => count($urlsMonetizacao),
                    'external_urls' => $urlsMonetizacao,
                ],
            ];

            \Log::info('[PMT] CANAL fim', [
                'channelId' => $channelId,
                'toxTodas' => count($toxTodas),
                'buckets' => count($bucketResults),
                'vidiq_monthly_avg_usd' => $vidiqMonthlyAvgUsd,
                'external_urls_count' => count($urlsMonetizacao),
            ]);
        }

        $this->mtResult = $resultado;

        \Log::info('[PMT] FIM avaliarMonetizacaoToxicidade', [
            'resultado_canais' => count($resultado),
        ]);

        \Log::info('[PMT] MT RESULT SUMMARY', collect($resultado)->map(function ($row, $channelId) {
            return [
                'channelId' => $channelId,
                'title' => $row['channel']['channelTitle'] ?? null,
                'tox_n' => $row['tox_canal']['n'] ?? 0,
                'tox_media' => $row['tox_canal']['media'] ?? null,
                'urls' => $row['monetizacao_canal']['external_urls_count'] ?? 0,
                'vidiq' => $row['monetizacao_canal']['vidiq_monthly_avg_usd'] ?? null,
                'buckets' => collect($row['buckets'] ?? [])->map(fn ($b) => [
                    'idx' => $b['idx'],
                    'label' => $b['label'],
                    'videos_count' => $b['analysis']['videos_count'] ?? 0,
                    'comments_n' => $b['analysis']['toxicity']['n'] ?? 0,
                    'tox_media' => $b['analysis']['toxicity']['media'] ?? null,
                    'sample_count' => count($b['analysis']['comentarios_sample'] ?? []),
                    'urls_media_por_video' => $b['analysis']['monetizacao_off_platform']['urls_media_por_video'] ?? 0,
                ])->values()->all(),
            ];
        })->values()->all());

    }

    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback' => $this->feedback,
            'polariz_media' => $this->polarizMediaArray,
            'mais_polarizado_real' => $this->maisPolarizadoReal,
        ];

        $status = 1;
        $finished_at = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));
        $msg = $t ? 'Obrigado! Sua tarefa #'.$tarefa_id.' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #'.$tarefa_id;
        $this->clearSelecionados();
        $this->msg($msg, 'info');
    }

    public function getComentariosVideo(string $videoId, int $max = 50): array
    {
        $key = env('YOUTUBE_API_KEY');
        $max = min($max, 50);

        $url = 'https://www.googleapis.com/youtube/v3/commentThreads'
            ."?key={$key}"
            ."&videoId={$videoId}"
            .'&part=snippet'
            .'&order=time'
            .'&textFormat=plainText'
            ."&maxResults={$max}";

        $resp = Http::timeout(35)->get($url);

        if ($resp->failed()) {
            return [];
        }

        $items = $resp->json('items') ?? [];

        return collect($items)->map(function ($item) {
            $snippet = data_get($item, 'snippet.topLevelComment.snippet', []);

            return [
                'commentId' => data_get($item, 'snippet.topLevelComment.id'),
                'text' => $snippet['textDisplay'] ?? '',
                'publishedAt' => $snippet['publishedAt'] ?? null,
                'author' => $snippet['authorDisplayName'] ?? null,
            ];
        })->filter(fn ($c) => ! empty($c['text']))->values()->all();
    }

    public function getAllVideos(
        string $channelId,
        int $max = 50
    ): array {
        $key = env('YOUTUBE_API_KEY');

        $max = min($max, 50);

        $url = 'https://www.googleapis.com/youtube/v3/search'
            ."?key={$key}"
            ."&channelId={$channelId}"
            .'&part=snippet'
            .'&order=date'
            .'&type=video'
            ."&maxResults={$max}";

        $resp = Http::timeout(35)->get($url);

        if ($resp->failed()) {
            return [];
        }

        $json = $resp->json();
        $items = $json['items'] ?? [];

        $videos = [];

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? [];
            $videoId = data_get($item, 'id.videoId');

            if (! $videoId) {
                continue;
            }

            $videos[] = [
                'videoId' => $videoId,
                'videoTitle' => (string) ($snippet['title'] ?? ''),
                'videoDesc' => (string) ($snippet['description'] ?? ''),
                'videoDt' => $snippet['publishedAt'] ?? null,
                'channelId' => $snippet['channelId'] ?? '',
                'channelTitle' => $snippet['channelTitle'] ?? '',
                'videoThumb' => data_get($snippet, 'thumbnails.medium.url'),
            ];
        }

        usort(
            $videos,
            fn ($a, $b) => strtotime($b['videoDt'] ?? '1970-01-01')
                <=>
                strtotime($a['videoDt'] ?? '1970-01-01')
        );

        return array_slice($videos, 0, $max);
    }

    #[Layout('layouts/app')]
    public function render()
    {
        return view('livewire.tarefa2');
    }
}
