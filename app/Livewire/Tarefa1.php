<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Services\YoutubeStorage;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Session;


class Tarefa1 extends Component
{

    public $buscas = []; // para armazenar os vídeos
    public array $comentarios = [];
    public array $selecionados = [];

    public string $addInput = '';

    #[Url()]
    public $query = '';


    public function mount()
    {
        // carrega seleção persistida
        $this->selecionados = Session::get('sel_videos', []);
    }


    protected function persistSelecionados(): void
    {
        // normaliza: únicos e sem vazios
        $this->selecionados = array_values(array_unique(array_filter($this->selecionados)));
        Session::put('sel_videos', $this->selecionados);
    }

    public function add(string $videoId): void
    {
        if (!in_array($videoId, $this->selecionados, true)) {
            $this->selecionados[] = $videoId;
            $this->persistSelecionados(); // já salva na sessão
        }
    }




    public function clearSelecionados(): void
    {
        $this->selecionados = [];
        $this->comentarios  = [];
        Session::forget('sel_videos');
    }

    public function removeSelecionado(string $videoId): void
    {
        $this->selecionados = array_values(array_diff($this->selecionados, [$videoId]));
        $this->persistSelecionados();
    }


    #[Computed]
    public function getSessaoVideosProperty33333333333333(): array
    {
        $idsSessao = \Illuminate\Support\Facades\Session::get('sel_videos', []);
        if (empty($idsSessao)) return [];

        // cache local da busca atual
        $cacheAtual = collect($this->buscas ?? [])->keyBy('videoId');

        $prontos = [];
        $faltantes = [];

        foreach ($idsSessao as $id) {
            if ($cacheAtual->has($id)) {
                $prontos[] = $cacheAtual->get($id);
            } else {
                $faltantes[] = $id;
            }
        }

        if (!empty($faltantes)) {
            $mais = $this->fetchVideosByIds($faltantes);
            $prontos = array_merge($prontos, $mais);
        }

        // manter a ordem dos IDs na sessão
        $byId = collect($prontos)->keyBy('videoId');
        return collect($idsSessao)->map(fn($id) => $byId->get($id))->filter()->values()->toArray();
    }
    
    
    public function addVideoByInput(): void
    {
        $input = trim($this->addInput);
        if ($input === '') return;

        #$id = null;
        $id = $this->parseVideoId($input);


        if (preg_match('~(?:v=|youtu\.be/)([A-Za-z0-9_-]{11})~', $input, $m)) {
            $id = $m[1];
        } elseif (preg_match('~^[A-Za-z0-9_-]{11}$~', $input)) {
            $id = $input;
        }

        if ($id && !in_array($id, $this->selecionados, true)) {
            $this->selecionados[] = $id;
            $this->persistSelecionados();
        }

        // limpa o campo após adicionar
        $this->addInput = '';
    }


    public function buscarComentarios()
    {
        $this->selecionados = Session::get('sel_videos', $this->selecionados);
        $this->comentarios = [];

        $storage = app(YoutubeStorage::class);

        // (1) registra/recupera a busca atual
        $busca = $storage->upsertBusca($this->query ?? '(manual)');

        foreach ($this->selecionados as &$vid) {
            $vid = $this->parseVideoId($vid) ?: $vid;
        }

        foreach ($this->selecionados as $videoId) {
            // (2) garantir canal + vídeo no BD
            $raw = $this->getVideoInfo($videoId); // sua função já existente
            // mapeia canal
            $canal = $storage->upsertCanal([
                'cod'        => $raw['channelId'],               // UC...
                'nome'       => $raw['channelTitle'] ?? null,
                'youtube_id' => $raw['channelHandle'] ?? null,   // se tiver
                'links'      => [
                    'yt'    => "https://www.youtube.com/channel/{$raw['channelId']}",
                    'vidiq' => "https://vidiq.com/youtube-stats/channel/{$raw['channelId']}",
                ],
                'inscritos'  => $raw['channelSubs'] ?? null,
                'views'      => $raw['channelViews'] ?? null,
                'videos'     => $raw['channelVideos'] ?? null,
                'dt'         => now(),
                'categ'      => $raw['channelCategory'] ?? null,
                'min'        => $raw['monet_min'] ?? null,
                'max'        => $raw['monet_max'] ?? null,
                'engagement' => $raw['engagement'] ?? null,
                'frequency'  => $raw['upload_frequency'] ?? null,
                'length'     => $raw['avg_length'] ?? null,
            ], $busca);

            // mapeia vídeo
            $video = $storage->upsertVideo($canal, [
                'cod'        => $videoId,
                'nome'       => $raw['title'] ?? null,
                'desc'       => $raw['description'] ?? null,
                'keywords'   => $raw['keywords'] ?? null,
                'comments'   => $raw['commentCount'] ?? null,
                'likes'      => $raw['likeCount'] ?? null,
                'views'      => $raw['viewCount'] ?? null,
                'duration'   => $raw['duration_seconds'] ?? null,
                'lang'       => $raw['lang'] ?? null,
                'dt'         => $raw['publishedAt'] ?? null,
            ], $busca);

            // (3) coletar comentários e ordenar
            $comentarios = $this->getAllComentarios($videoId); // precisa devolver 'cod'
            $ordenados = collect($comentarios)
                ->sortBy(fn($c) => $c['dt'] ?? $c['publishedAt'] ?? now())
                ->values()
                ->toArray();

            // (4) persistir
            $storage->upsertComentarios($video, array_map(function ($c) {
                return [
                    'cod'      => $c['id'] ?? $c['cod'] ?? null, // commentId
                    'user'     => $c['author'] ?? $c['user'] ?? null,
                    'texto'    => $c['text'] ?? $c['texto'] ?? null,
                    'likes'    => $c['likeCount'] ?? $c['likes'] ?? null,
                    'dislikes' => $c['dislikes'] ?? null,
                    'dt'       => $c['publishedAt'] ?? $c['dt'] ?? null,
                    'tox'      => $c['tox'] ?? null,
                ];
            }, $ordenados));

            // (5) exibir no componente
            $this->comentarios[$videoId] = $ordenados;
        }
    }







    protected function getVideoInfo(string $videoId): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        // 1) Dados do vídeo
        $urlVideo = 'https://www.googleapis.com/youtube/v3/videos?' . http_build_query([
            'key'   => $apiKey,
            'id'    => $videoId,
            'part'  => 'snippet,statistics,contentDetails', // o necessário
            // topicDetails/etc. não é essencial aqui
        ]);

        $respV = @file_get_contents($urlVideo);
        $jV    = json_decode($respV, true);
        $item  = $jV['items'][0] ?? null;
        if (!$item) return [];

        $snip    = $item['snippet'] ?? [];
        $stats   = $item['statistics'] ?? [];
        $details = $item['contentDetails'] ?? [];

        $channelId    = $snip['channelId'] ?? null;
        $channelTitle = $snip['channelTitle'] ?? null;

        // 2) Dados do canal (para inscritos/views totais, país etc.)
        $ch = [];
        if ($channelId) {
            $urlCh = 'https://www.googleapis.com/youtube/v3/channels?' . http_build_query([
                'key'  => $apiKey,
                'id'   => $channelId,
                'part' => 'snippet,statistics,brandingSettings,contentDetails',
            ]);
            $respC = @file_get_contents($urlCh);
            $jC    = json_decode($respC, true);
            $chI   = $jC['items'][0] ?? null;

            if ($chI) {
                $chSnip  = $chI['snippet'] ?? [];
                $chStats = $chI['statistics'] ?? [];
                $branding = $chI['brandingSettings']['channel'] ?? [];

                $ch = [
                    'channelSubs'    => isset($chStats['subscriberCount']) ? (int)$chStats['subscriberCount'] : null,
                    'channelViews'   => isset($chStats['viewCount']) ? (int)$chStats['viewCount'] : null,
                    'channelVideos'  => isset($chStats['videoCount']) ? (int)$chStats['videoCount'] : null,
                    'channelCategory' => $chSnip['country'] ?? null, // usa país como proxy de "categoria/local"
                    'channelHandle'  => $branding['unsubscribedTrailer'] ?? null, // não há handle oficial aqui; deixa null
                ];
            }
        }

        // 3) Monta retorno no formato esperado pelo storage
        return array_merge([
            'channelId'        => $channelId,
            'channelTitle'     => $channelTitle,
            'title'            => $snip['title'] ?? null,
            'description'      => $snip['description'] ?? null,
            'keywords'         => null, // a API não entrega keywords de SEO; deixe null
            'commentCount'     => isset($stats['commentCount']) ? (int)$stats['commentCount'] : null,
            'likeCount'        => isset($stats['likeCount']) ? (int)$stats['likeCount'] : null,
            'viewCount'        => isset($stats['viewCount']) ? (int)$stats['viewCount'] : null,
            'duration_seconds' => $this->ISO8601ToSeconds($details['duration'] ?? null),
            'lang'             => $snip['defaultAudioLanguage'] ?? ($snip['defaultLanguage'] ?? null),
            'publishedAt'      => $snip['publishedAt'] ?? null,
            // campos específicos seus (quando não vindos da API, deixam-se null)
            'monet_min'        => null,
            'monet_max'        => null,
            'engagement'       => null,
            'upload_frequency' => null,
            'avg_length'       => null,
        ], $ch);
    }








    // Tarefa1.php (dentro da classe)

    protected function fetchVideosByIds444444444444444444(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) return [];

        $apiKey = env('YOUTUBE_API_KEY');
        $chunks = array_chunk($ids, 50); // API permite até 50 IDs por chamada
        $out = [];

        foreach ($chunks as $pack) {
            $url = "https://www.googleapis.com/youtube/v3/videos"
                . "?part=snippet,statistics,contentDetails&id=" . implode(',', $pack)
                . "&key={$apiKey}";

            $resp = @file_get_contents($url);
            if ($resp === false) continue;

            $json  = json_decode($resp, true);
            $items = $json['items'] ?? [];

            foreach ($items as $v) {
                $id      = $v['id'] ?? null;
                $snip    = $v['snippet'] ?? [];
                $stats   = $v['statistics'] ?? [];
                $details = $v['contentDetails'] ?? [];

                $out[] = [
                    'videoId'   => $id,
                    'title'     => $snip['title'] ?? '',
                    'channel'   => $snip['channelTitle'] ?? '',
                    'published' => $snip['publishedAt'] ?? '',
                    'thumbnail' => $snip['thumbnails']['default']['url'] ?? '',
                    'views'     => $stats['viewCount'] ?? null,
                    'likes'     => $stats['likeCount'] ?? null,
                    'comments'  => $stats['commentCount'] ?? 0,
                    'duration'  => $this->ISO8601ToSeconds($details['duration'] ?? null),
                ];
            }
        }

        return $out;
    }




    // No componente Tarefa1
    public function getIrProperty3333333333333()
    {
        // indexa buscas por videoId para pegar o total de comentários
        $buscasById = collect($this->buscas)->keyBy('videoId');

        $map = [];
        foreach ($this->selecionados ?? [] as $vid) {
            $threads = $this->comentarios[$vid] ?? []; // só existe se já buscou comentários
            $top     = is_countable($threads) ? count($threads) : 0;
            $total   = data_get($buscasById, "$vid.comments");

            $map[$vid] = ($total !== null && $top > 0)
                ? round(($total - $top) / $top, 2)
                : null; // ainda não dá pra calcular
        }
        return $map; // acessível na blade como $this->ir
    }

    


    protected function parseVideoId(string $ref): ?string
    {
        $ref = trim($ref);
        // aceita 3 formatos: ID cru, youtu.be/ID, ...watch?v=ID...
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $ref)) return $ref;

        // youtu.be/ID
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~i', $ref, $m)) return $m[1];

        // watch?v=ID  (pega exatamente 11 chars após v=)
        if (preg_match('~[?&]v=([A-Za-z0-9_-]{11})~i', $ref, $m)) return $m[1];

        // fallback (últimos 11 que parecem ID)
        if (preg_match('~([A-Za-z0-9_-]{11})$~', $ref, $m)) return $m[1];

        return null;
    }



    protected function getAllComentarios($videoId, $pageToken = null): array
    {
        $url = "https://www.googleapis.com/youtube/v3/commentThreads";

        $params = [
            'key'        => env('YOUTUBE_API_KEY'),
            'part'       => 'snippet',
            'maxResults' => 100,
            'videoId'    => $videoId,
            'textFormat' => 'plainText',
            'order'      => 'time', // opcional
        ];
        if ($pageToken) $params['pageToken'] = $pageToken;

        $call = $url . '?' . http_build_query($params);
        $response = @file_get_contents($call);
        $data = json_decode($response, true);

        $lote = collect($data['items'] ?? [])->map(function ($item) {
            $top = $item['snippet']['topLevelComment'] ?? [];
            $sn  = $top['snippet'] ?? [];

            // id do comentário top-level:
            $commentId = $top['id'] ?? null; // preferível
            if (!$commentId) {
                // fallback (não deveria precisar):
                $commentId = $item['id'] ?? null;
            }

            return [
                'cod'  => $commentId,                          // <- usado como unique
                'user' => $sn['authorDisplayName'] ?? null,
                'texto' => $sn['textDisplay'] ?? '',
                'likes' => $sn['likeCount'] ?? 0,
                'dislikes' => null,                            // YT não traz
                'dt'   => $sn['publishedAt'] ?? null,
                'tox'  => $this->setTox($sn['textDisplay'] ?? ''), // Perspective
            ];
        })->toArray();

        // recursão se houver próxima página
        if (!empty($data['nextPageToken'])) {
            return array_merge(
                $lote,
                $this->getAllComentarios($videoId, $data['nextPageToken'])
            );
        }

        return $lote;
    }







    function setTox($txt)
    {

        $apiKey = env("PERSPECTIVE_API");

        $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . $apiKey;

        $payload = [
            'comment' => ['text' => $txt],
            'languages' => ['pt', 'en'], // ou 'pt' se quiser
            'requestedAttributes' => [
                'TOXICITY' => new \stdClass()
            ]
        ];

        $json = json_encode($payload);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            dd('Erro cURL: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $res = json_decode($response, true);

        if (!is_array($res)) {
            return;
        }


        if (isset($res['attributeScores']['TOXICITY']['summaryScore']['value'])) {
            $tox = round($res['attributeScores']['TOXICITY']['summaryScore']['value'], 3);
        } else {
            $tox = null; // ou 0, ou -1, ou qualquer valor que faça sentido no seu contexto
        }
        return $tox;
    }

    public function updatedQuery($value)
    {
        $this->getVideos();
    }


    ##[Computed()]
    public function getVideos()
    {

        if (empty($this->query)) {
            return $this->buscas;
        }

        $query = $this->query;
        $apiKey = env('YOUTUBE_API_KEY');

        // 1. Buscar vídeos por termo
        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . 
        urlencode($query) . "&type=video&maxResults=20&key=$apiKey";
        $response = file_get_contents($url);
        $data = json_decode($response, true);


        if (!isset($data['items'])) {
            $this->buscas = [];
            return;
        } else {
            // 2. Extrair IDs
            $videoIds = collect($data['items'])->pluck('id.videoId')->filter()->toArray();
            if (empty($videoIds)) {
                $this->buscas = [];
                return;
            }

            // 3. Buscar estatísticas
            $videoUrl = "https://www.googleapis.com/youtube/v3/videos?part=statistics,contentDetails&id=" . implode(',', $videoIds) . "&key=$apiKey";

            $videoResponse = file_get_contents($videoUrl);
            $videoData = json_decode($videoResponse, true);

            // 4. Indexar por ID
            $statsById = collect($videoData['items'] ?? [])->keyBy('id');

            $this->buscas = collect($data['items'])->map(function ($item) use ($statsById) {
                $id = $item['id']['videoId'] ?? null;
                $stats = $statsById[$id]['statistics'] ?? [];
                $duration = $statsById[$id]['contentDetails']['duration'] ?? null;

                return [
                    'videoId'    => $id,
                    'title'      => $item['snippet']['title'] ?? '',
                    'channel'    => $item['snippet']['channelTitle'] ?? '',
                    'published'  => $item['snippet']['publishedAt'] ?? '',
                    'thumbnail'  => $item['snippet']['thumbnails']['default']['url'] ?? '',
                    'views'      => $stats['viewCount'] ?? null,
                    'likes'      => $stats['likeCount'] ?? null,
                    'comments'   => $stats['commentCount'] ?? 0,
                    'duration'   => $this->ISO8601ToSeconds($duration),

                ];
            })
                ->filter(fn($video) => $video['comments'] > 0)
                ->values() // reindexa os índices
                ->toArray();

            return $this->buscas;
        }
    }


    protected function ISO8601ToSeconds($duration)
    {
        if (!$duration) return null;

        $interval = new \DateInterval($duration);
        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }



    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa1');
    }
}
