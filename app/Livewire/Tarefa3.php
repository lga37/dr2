<?php

namespace App\Livewire;

use DOM\HtmlDocument;
use Livewire\Component;
use Livewire\Attributes\Url;

use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Tarefa3 extends Component
{
    public $buscas = [];
    public array $canais = [];
    public array $monets = [];
    public array $arxivs = [];
    public array $selecionados = [];

    #[Url()]
    public $query = '';

    public function mount()
    {
        // Carrega canais da sessão ao montar
        $this->canais = Session::get('canaisSelecionados', []);
        $this->selecionados = array_keys($this->canais);
    }
    public function updated($property)
    {
        if (str_starts_with($property, 'selecionados')) {
            $this->selecionados = array_values(array_filter($this->selecionados));

            $sessao = Session::get('canaisSelecionados', []);

            // Adiciona novos selecionados
            foreach ($this->selecionados as $canalId) {
                if (!isset($sessao[$canalId]) && isset($this->buscas[$canalId])) {
                    $sessao[$canalId] = $this->buscas[$canalId];
                }
            }

            // Remove desmarcados
            foreach (array_keys($sessao) as $id) {
                if (!in_array($id, $this->selecionados)) {
                    unset($sessao[$id]);
                }
            }

            Session::put('canaisSelecionados', $sessao);
            $this->canais = $sessao;
        }
    }


    public function updatedQuery($value)
    {
        $this->getCanais();
    }




    public function toggleCanal($canalId)
    {
        $sessao = Session::get('canaisSelecionados', []);
        $buscas = collect($this->buscas)->keyBy('canalId');

        // Alterna: se já existe, remove; senão, adiciona
        if (isset($sessao[$canalId])) {
            unset($sessao[$canalId]);
        } elseif ($buscas->has($canalId)) {
            $sessao[$canalId] = $buscas[$canalId];
        }

        // Atualiza sessão e variáveis Livewire
        Session::put('canaisSelecionados', $sessao);
        $this->canais = $sessao;
        $this->selecionados = array_keys($sessao);
    }



    public function getCanais()
    {
        if (empty($this->query)) {
            return;
        }

        $apiKey = env('YOUTUBE_API_KEY');
        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($this->query) . "&type=video&maxResults=20&key=$apiKey";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $canalIds = collect($data['items'])->pluck('snippet.channelId')->unique()->filter()->toArray();

        if (empty($canalIds)) return;

        $canalUrl = "https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id=" . implode(',', $canalIds) . "&key=$apiKey";
        $canalResponse = file_get_contents($canalUrl);
        $canalData = json_decode($canalResponse, true);

        $this->buscas = collect($canalData['items'] ?? [])->mapWithKeys(function ($item) {
            $id = $item['id'] ?? '';
            return [
                $id => [
                    'canalId'    => $id,
                    'title'      => $item['snippet']['title'] ?? '',
                    'pais'       => $item['snippet']['country'] ?? 'n/a',
                    'views'      => $item['statistics']['viewCount'] ?? 0,
                    'comments'   => $item['statistics']['commentCount'] ?? 0,
                    'videos'     => $item['statistics']['videoCount'] ?? 0,
                    'inscritos'  => $item['statistics']['subscriberCount'] ?? 0,
                    'published'  => $item['snippet']['publishedAt'] ?? '',
                    'thumbnail'  => $item['snippet']['thumbnails']['default']['url'] ?? '',
                ]
            ];
        })->toArray();
    }

    public function selecionar()
    {
        $atual = Session::get('canaisSelecionados', []);

        foreach ($this->selecionados as $id) {
            if (!isset($atual[$id]) && isset($this->buscas[$id])) {
                $atual[$id] = $this->buscas[$id];
            }
        }

        Session::put('canaisSelecionados', $atual);
        $this->canais = $atual;
    }

    public function limparCanais()
    {
        Session::forget('canaisSelecionados');
        $this->canais = [];
        $this->selecionados = [];
    }

    public function buscarMonets()
    {
        $this->monets = [];

        foreach ($this->selecionados as $canalId) {

            $arxivs = $this->getInscritos($canalId);
            $this->arxivs[$canalId] = $arxivs;

            #dd($this->arxivs);

            $monets = $this->getMonets($canalId);
            $this->monets[$canalId] = $monets;
        }

    }

    protected function getMonets($canalId)
    {

        $url = "https://vidiq.com/youtube-stats/channel/$canalId/";

        #dump($url);

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                return ['erro' => 'Erro ao acessar página Vidiq'];
            }

            $body = $response->body();
            $html_body = HtmlDocument::createFromString($body);

            $main = $html_body->querySelector('main');
            $html = $main->innerHTML;
            #dd($main->innerHTML);

            $re_dirs = [
                #'score'      => 'Overall Score:<\/p><p class.+>(\w{1})<\/span><\/p>',
                #'min_max'    => '\$<!-- -->([\d\.KM]+)<!-- --> - \$<!-- -->([\d\.KM]+)<\/p>',
                'min_max'    => '<span>\$<!-- -->([\d\.,KM]+)<!-- --> - \$<!-- -->([\d\.,KM]+)<\/span>',
                'frequency'  => '<p[^>]*>\s*([\d\.]+)\s*<!-- -->\s*\/\s*<!-- -->\s*week\s*<\/p>',
                'length'     => 'Average Video Length.*?<p[^>]*>\s*([\d\.]+)\s*(?:<!--.*?-->)*\s*Minutes\s*<\/p>',
                'engagement' => 'Engagement Rate:<\/p>.+>([\d\.]+)<!-- -->%<\/p>',
            ];

            $html = limpaEspacosAcentuacao($html);

            #dd($html);

            $campos = [
                #'score' => null,
                'min' => null,
                'max' => null,
                'engagement' => null,
                'frequency' => null,
                'length' => null
            ];

            foreach ($re_dirs as $key => $re) {
                if (preg_match('/' . $re . '/', $html, $res)) {
                    if ($key == 'min_max') {
                        $campos['min'] = return_kmb_to_integer($res[1]);
                        $campos['max'] = return_kmb_to_integer($res[2]);
                    } else {
                        $campos[$key] = return_kmb_to_integer($res[1]);
                    }
                }
            }

            // Conversões finais
            $campos['length'] = return_kmb_to_integer($campos['length']);

            #dd($campos);

            return $campos;
        } catch (\Exception $e) {
            return ['erro' => 'Falha no scraping: ' . $e->getMessage()];
        }
    }


    public function getInscritos($youtube_id): array
    {
        $pairs  = $this->getWaybackSamples($youtube_id, 10); // amostra de 10
        $result = $this->scrapeSubscribersFromSamples($pairs);
        return $result;
    }

    protected function getWaybackSamples(string $youtubeId, int $sampleSize = 10): array
    {

        $params = [
            // use o host completo; o CDX casa melhor que "youtube.com"
            'url'       => "https://www.youtube.com/channel/{$youtubeId}",
            'matchType' => 'exact',
            'output'    => 'json',
            'fl'        => 'timestamp,original,statuscode,mimetype,digest',
            'from'      => '20140101',
            'to'        => '20250606',

            // mantenha só o filtro negativo pra robots; remova statuscode/mimetype
            'filter'    => ['!original:*robots.txt*'],

            // reduza densidade temporal e dedupe conteúdo
            'collapse'  => ['timestamp:6', 'digest'],

            // pegue bastante e, se ainda vier muito, você amostra depois no PHP
            'limit'     => 50000,
        ];

        // monte a URL
        $cdx = "https://web.archive.org/cdx/search/cdx?" . http_build_query($params);


        #echo "\n\n\n$cdx\n\n\n";



        $resp = Http::timeout(12)->retry(3, 300)->get($cdx);
        $list = $resp->json() ?? [];
        $rows = array_slice($list, 1); // primeira linha é o header

        $pairs = array_map(fn($r) => [
            'ts'  => $r[0],     // timestamp (porque fl=timestamp,original,statuscode,mimetype,digest)
            'url' => "https://web.archive.org/web/{$r[0]}if_/{$r[1]}",
        ], $rows);

        // dedup (timestamp|url) só por garantia
        $pairs = collect($pairs)->unique(fn($p) => $p['ts'] . '|' . $p['url'])->values()->all();

        // embaralha e pega amostra
        shuffle($pairs);
        $sample = array_slice($pairs, 0, $sampleSize);

        return $sample;
    }

    protected function scrapeSubscribersFromSamples(array $pairs): array
    {
        $result = [];

        foreach ($pairs as $p) {
            $ts  = $p['ts'];
            $url = $p['url'];

            try {
                $res = Http::timeout(17)->retry(4, 1000)->get($url);
                if (!$res->ok())
                    continue; // evita 404 da Wayback
                $html = $res->body();

                // (opcional) pular idiomas proibidos
                if (preg_match('/<html[^>]*lang="([a-z]{2})"/i', $html, $m)) {
                    $lang = strtolower($m[1]);
                    if (in_array($lang, ['ru'])) continue;
                }

                // regex/JSON fallback
                $subs = null;
                $regexSubs = [
                    '/<span[^>]*class="[^"]*yt-subscription-button-subscriber-count-branded-horizontal[^"]*subscribed[^"]*"[^>]*>([\d\.,]+)<\/span>/i',
                    '/"subscriberCountText":\{"simpleText":"([\d\.,KMkm]+) subscribers"\}/',
                ];

                foreach ($regexSubs as $re) {
                    if (preg_match($re, $html, $m)) {
                        $subs = retornaMilMilhaoBilhaoToInt($m[1]);
                        break;
                    }
                }

                if ($subs && $subs > 0) {
                    $result[$ts] = $subs;
                }
            } catch (\Throwable $e) {
                echo 'Wayback scrape erro' . $url . ' e: ' . $e->getMessage();
            }
        }

        ksort($result); // ordena por tempo
        return $result;
    }




    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa3');
    }
}
