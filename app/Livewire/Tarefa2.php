<?php

namespace App\Livewire;

use Log;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Cache;


class Tarefa2 extends Component
{

    public $buscas = []; // para armazenar os vídeos
    public array $canais = [];
    public array $selecionados = [];


    #[Url()]
    public $query = '';


    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'selecionados')) {
            $this->selecionados = array_values(array_filter($this->selecionados));
        }
    }


    public function updatedQuery($value)
    {
        $this->getCanais();
    }



    public function getCanais()
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

        if (empty($data['items'])) {
            $this->buscas = [];
            return;
        }

        // 2. Extrair os canalIds dos vídeos encontrados
        $canalIds = collect($data['items'])->pluck('snippet.channelId')->unique()->filter()->toArray();

        if (empty($canalIds)) {
            $this->buscas = [];
            return;
        }

        // 3. Buscar dados dos canais
        $canalUrl = "https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id=" . implode(',', $canalIds) . "&key=$apiKey";
        $canalResponse = file_get_contents($canalUrl);
        $canalData = json_decode($canalResponse, true);

        $this->buscas = collect($canalData['items'] ?? [])->map(function ($item) {
            $stats = $item['statistics'] ?? [];
            $snippet = $item['snippet'] ?? [];

            return [
                'canalId'    => $item['id'] ?? null,
                'title'      => $snippet['title'] ?? '',
                'pais'       => $snippet['country'] ?? 'n/a',
                'views'      => $stats['viewCount'] ?? 0,
                'likes'      => null, // não disponível na API
                'comments'   => $stats['commentCount'] ?? 0,
                'videos'     => $stats['videoCount'] ?? 0,
                'inscritos'  => $stats['subscriberCount'] ?? 0,
                'published'  => $snippet['publishedAt'] ?? '',
                'thumbnail'  => $snippet['thumbnails']['default']['url'] ?? '',
            ];
        })->toArray();

        return $this->buscas;
    }



    function nlp33333333(string $texto): ?float
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

        return round($res['sentiment']['score'], 2);
    }




    function nlp(string $texto): ?float
    {
        $texto = trim($texto);
        if ($texto === '') return null;

        // opcional: limitar tamanho pra evitar timeout/quotas
        if (mb_strlen($texto) > 3000) {
            $texto = mb_substr($texto, 0, 3000);
        }

        // cache 30 dias por hash do texto
        $cacheKey = 'nlp:' . sha1($texto);
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($texto) {
            $url   = 'https://api.gotit.ai/NLU/v1.5/Analyze';
            $basic = env('GOTIT_API_KEY') . ':' . env('GOTIT_SECRET_KEY');

            $payload = json_encode(["T" => $texto, "S" => true, "EM" => true]);
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
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                Log::warning('NLP timeout/erro', ['err' => curl_error($ch)]);
                curl_close($ch);
                return null;
            }
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http !== 200) {
                Log::warning('NLP HTTP não-200', ['http' => $http, 'body' => $result]);
                return null;
            }

            $res = json_decode($result, true);
            $score = data_get($res, 'sentiment.score'); // pode vir 0 (falsy), então não use empty()
            return is_numeric($score) ? round((float)$score, 2) : null;
        });
    }







    public function buscarVideos444444()
    {
        $this->canais = [];

        foreach ($this->selecionados as $canalId) {
            $videos = $this->getAllVideos($canalId);

            // Ordena do mais antigo para o mais recente
            $ordenados = collect($videos)->sortBy('publishedAt')->values()->toArray();

            $this->canais[$canalId] = $ordenados;
        }
    }


    protected function avgNullable(array $vals): ?float
    {
        $nums = array_values(array_filter($vals, fn($v) => $v !== null));
        if (!count($nums)) return null;
        return round(array_sum($nums) / count($nums), 2);
    }

    public function buscarVideos()
    {
        $this->canais = [];

        foreach ($this->selecionados as $canalId) {
            $videos = $this->getAllVideos($canalId);

            // Ordena do mais antigo para o mais recente
            $ordenados = collect($videos)->sortBy('publishedAt')->values();

            // Anexa scores NLP (título/descrição) e média
            $scored = $ordenados->map(function ($v) {
                $t = $this->nlp($v['title'] ?? '');
                $d = $this->nlp($v['desc']  ?? '');
                // opcional: respiro pra não estourar rate (ajuste se precisar)
                usleep(150000); // 150ms
                return $v + [
                    'nlp_title' => $t,
                    'nlp_desc'  => $d,
                    'nlp_mean'  => $this->avgNullable([$t, $d]),
                ];
            })->toArray();

            $this->canais[$canalId] = $scored;
        }
    }





    protected function getAllVideos($canalId, $pageToken = null): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        // 1. Buscar IDs dos vídeos do canal
        $url = "https://www.googleapis.com/youtube/v3/search";
        $params = [
            'key' => $apiKey,
            'channelId' => $canalId,
            'part' => 'snippet',
            'order' => 'date',
            'maxResults' => 50,
            'type' => 'video',
        ];
        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $res = file_get_contents($url . '?' . http_build_query($params));
        $data = json_decode($res, true);

        $videoList = $data['items'] ?? [];

        // 2. Obter os IDs dos vídeos encontrados
        $videoIds = collect($videoList)->pluck('id.videoId')->filter()->implode(',');

        if (empty($videoIds)) return [];

        // 3. Buscar detalhes de cada vídeo
        $url2 = "https://www.googleapis.com/youtube/v3/videos";
        $params2 = [
            'key' => $apiKey,
            'id' => $videoIds,
            'part' => 'snippet,statistics,contentDetails',
        ];

        $res2 = file_get_contents($url2 . '?' . http_build_query($params2));
        $data2 = json_decode($res2, true);

        $videos = collect($data2['items'] ?? [])->map(function ($v) {
            return [
                'videoId' => $v['id'],
                'title' => $v['snippet']['title'] ?? '',
                'desc' => $v['snippet']['description'] ?? '',
                'publishedAt' => $v['snippet']['publishedAt'] ?? '',
                'viewCount' => $v['statistics']['viewCount'] ?? 0,
                'likeCount' => $v['statistics']['likeCount'] ?? 0,
                'commentCount' => $v['statistics']['commentCount'] ?? 0,
                'duration' => $v['contentDetails']['duration'] ?? 'PT0M0S',
            ];
        })->toArray();

        // Requisição recursiva para próxima página
        if (!empty($data['nextPageToken'])) {
            return array_merge(
                $videos,
                $this->getAllVideos($canalId, $data['nextPageToken'])
            );
        }

        return $videos;
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
        return view('livewire.tarefa2');
    }
}
