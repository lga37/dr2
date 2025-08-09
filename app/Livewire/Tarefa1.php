<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;


class Tarefa1 extends Component
{

    public $buscas = []; // para armazenar os vídeos
    public array $comentarios = [];
    public array $selecionados = [];


    #[Url()]
    public $query = '';


    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'selecionados')) {
            $this->selecionados = array_values(array_filter($this->selecionados));
        }
    }

    public function buscarComentarios()
    {
        $this->comentarios = [];

        foreach ($this->selecionados as $videoId) {
            $comentarios = $this->getAllComentarios($videoId);

            // Ordena do mais antigo para o mais recente (se houver publishedAt)
            $ordenados = collect($comentarios)->sortBy(function ($c) {
                return $c['publishedAt'] ?? now(); // fallback pra não dar erro
            })->values()->toArray();

            $this->comentarios[$videoId] = $ordenados;
        }

        #dd($this->comentarios);
    }

    // No componente Tarefa1
    public function getIrProperty()
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




    function nlp(string $texto): ?float
    {
        if (empty(trim($texto))) return null;

        $url = 'https://api.gotit.ai/NLU/v1.5/Analyze';
        $basic = env('GOTIT_API_KEY') . ':' . env('GOTIT_SECRET_KEY');

        $payload = json_encode([
            "T" => $texto,
            "S" => true,
            "EM" => true,
        ]);

        $headers = [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($basic),
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $result = curl_exec($ch);

        #dd($result);

        if (curl_errno($ch)) {
            dd('Erro cURL NLP: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $res = json_decode($result, true);


        if (!is_array($res) || empty($res['sentiment']['score'])) {
            dump($res);
            dd('Resposta inválida da API NLP');
            return null;
        }

        return round($res['sentiment']['score'], 2) ?? null;
    }



    protected function getAllComentarios($videoId, $pageToken = null): array
    {
        $url = "https://www.googleapis.com/youtube/v3/commentThreads";

        $params = [
            'key' => env('YOUTUBE_API_KEY'),
            'part' => 'snippet',
            'maxResults' => 100,
            'videoId' => $videoId,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $call = $url . '?' . http_build_query($params);

        $response = file_get_contents($call);
        $data = json_decode($response, true);

        $comentarios = collect($data['items'] ?? [])->map(function ($item) {
            $snippet = $item['snippet']['topLevelComment']['snippet'];
            return [
                'text' => $snippet['textDisplay'],
                'publishedAt' => $snippet['publishedAt'],
                'likeCount' => $snippet['likeCount'] ?? 0,
                'toxicity' => $this->setTox($snippet['textDisplay']), // aqui chamamos o Perspective

            ];
        })->toArray();

        // recursão se houver próxima página
        if (!empty($data['nextPageToken'])) {
            return array_merge(
                $comentarios,
                $this->getAllComentarios($videoId, $data['nextPageToken'])
            );
        }

        return $comentarios;
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

        #dd($res);

        #$tox = round($res['attributeScores']['TOXICITY']['summaryScore']['value'], 3) ?? null;

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
        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($query) . "&type=video&maxResults=20&key=$apiKey";
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

                    #'title_sentiment' => $this->nlp($item['snippet']['title']),
                    #'description_sentiment' => $this->nlp($item['snippet']['description'] ?? ''),

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
