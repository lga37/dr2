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

    public array $monets = [];
    public array $arxivs = [];

    public $buscas = [];
    public array $canais = [];
    public array $selecionados = [];
   
    #[Url()]
    public $query = '';
    public string $addInput = '';

    public function mount()
    {
        $this->selecionados = Session::get('sel_canais_tarefa3', []);
    }

    protected function persistSelecionados(): void
    {
        $this->selecionados = array_values(array_unique(array_filter($this->selecionados)));
        Session::put('sel_canais_tarefa3', $this->selecionados);
    }

    public function add(string $canalId): void
    {
        if (!in_array($canalId, $this->selecionados, true)) {
            $this->selecionados[] = $canalId;
            $this->persistSelecionados();
        }
    }

    public function removeSelecionado(string $canalId): void
    {
        $this->selecionados = array_values(array_diff($this->selecionados, [$canalId]));
        $this->persistSelecionados();
    }

    public function clearSelecionados(): void
    {
        $this->selecionados = [];
        $this->canais = [];
        Session::forget('sel_canais_tarefa3');
    }

    public function addCanalByInput(): void
    {
        $input = trim($this->addInput);
        if ($input === '') return;

        $id = $this->parseCanalIdentifier($input);
        if ($id) {
            $this->add($id);
            $this->addInput = '';
        }
    }

    public function updatedQuery()
    {
        $this->getCanais();
    }


    public function getCanais()
    {
        if (empty($this->query)) {
            return $this->buscas;
        }

        $query = $this->query;

        #dd($query);
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

        #dd($this->buscas);

        return $this->buscas;
    }


    public function getSessaoCanaisProperty(): array
    {
        $idsSessao = Session::get('sel_canais_tarefa3', []);
        if (empty($idsSessao)) return [];

        // cache local da listagem atual
        $cacheAtual = collect($this->buscas ?? [])->keyBy('canalId');

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
            $mais = $this->fetchCanaisByIds($faltantes);
            $prontos = array_merge($prontos, $mais);
        }

        // manter a ordem dos IDs na sessão
        $byId = collect($prontos)->keyBy('canalId');
        return collect($idsSessao)->map(fn($id) => $byId->get($id))->filter()->values()->toArray();
    }

    protected function fetchCanaisByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) return [];

        $apiKey = env('YOUTUBE_API_KEY');
        $out = [];

        foreach (array_chunk($ids, 50) as $pack) {
            $url = 'https://www.googleapis.com/youtube/v3/channels?' . http_build_query([
                'part' => 'snippet,statistics',
                'id'   => implode(',', $pack),
                'key'  => $apiKey,
            ]);

            $resp = @file_get_contents($url);
            if ($resp === false) continue;
            $json = json_decode($resp, true);

            foreach ($json['items'] ?? [] as $item) {
                $stats   = $item['statistics'] ?? [];
                $snippet = $item['snippet'] ?? [];
                $out[] = [
                    'canalId'   => $item['id'] ?? null,
                    'title'     => $snippet['title'] ?? '',
                    'pais'      => $snippet['country'] ?? 'n/a',
                    'views'     => $stats['viewCount'] ?? 0,
                    'comments'  => $stats['commentCount'] ?? 0,
                    'videos'    => $stats['videoCount'] ?? 0,
                    'inscritos' => $stats['subscriberCount'] ?? 0,
                    'published' => $snippet['publishedAt'] ?? '',
                    'thumbnail' => $snippet['thumbnails']['default']['url'] ?? '',
                ];
            }
        }

        return $out;
    }

    protected function parseCanalIdentifier(string $input): ?string
    {
        // ID começa com UC + 22 chars
        if (preg_match('~^(UC[A-Za-z0-9_-]{22})$~', $input, $m)) {
            return $m[1];
        }
        // URL /channel/UC...
        if (preg_match('~channel/(UC[A-Za-z0-9_-]{22})~', $input, $m)) {
            return $m[1];
        }
        // @handle (ou URL com /@handle)
        if (preg_match('~@([A-Za-z0-9._-]{3,30})~', $input, $m)) {
            return $this->resolveChannelIdBySearch('@' . $m[1]);
        }
        // fallback: tentar achar pelo nome
        return $this->resolveChannelIdBySearch($input);
    }

    protected function resolveChannelIdBySearch(string $q): ?string
    {
        $apiKey = env('YOUTUBE_API_KEY');
        $url = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
            'part'       => 'snippet',
            'type'       => 'channel',
            'maxResults' => 1,
            'q'          => $q,
            'key'        => $apiKey,
        ]);

        $resp = @file_get_contents($url);
        if ($resp === false) return null;
        $data = json_decode($resp, true);
        return $data['items'][0]['id']['channelId'] ?? null;
    }


    #1  ###################################################################################
    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa3');
    }
    
    public function buscarDados(){

        $this->buscarMonets();
    }


    public function buscarMonets()
    {
        $this->monets = [];

        foreach ($this->selecionados as $canalId) {

            #$arxivs = $this->getInscritos($canalId);
            #$this->arxivs[$canalId] = $arxivs;

            #dd($this->arxivs);

            $monets = $this->getMonets($canalId);
            $this->monets[$canalId] = $monets;
        }

    }

    protected function getMonets($canalId)
    {

        $url = "https://vidiq.com/youtube-stats/channel/$canalId/";

        dump($url);

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                return ['erro' => 'Erro ao acessar página Vidiq'];
            }

            $html = $response->body();

            #dump($html);
            $html_document = HtmlDocument::createFromString($html);
            $body = $html_document->body; 
            dump($body);

            $html = $body->innerHTML;

            #$main = $body->querySelector('main');
            #dump($main);
            #$html = $main->innerHTML;
            #dd($html);

            $re_dirs = [
                #'score'      => 'Overall Score:<\/p><p class.+>(\w{1})<\/span><\/p>',
                #'min_max'    => '\$<!-- -->([\d\.KM]+)<!-- --> - \$<!-- -->([\d\.KM]+)<\/p>',
                'min_max'    => 'Est\.\s*Monthly\s*Earnings[\s\S]*?<span[^>]*>\s*\$([\d.,]+[KMB]?)\s*[-–]\s*\$([\d.,]+[KMB]?)\s*<\/span>',
                #'frequency'  => '<p[^>]*>\s*([\d\.]+)\s*<!-- -->\s*\/\s*<!-- -->\s*week\s*<\/p>',
                #'length'     => 'Average Video Length.*?<p[^>]*>\s*([\d\.]+)\s*(?:<!--.*?-->)*\s*Minutes\s*<\/p>',
                #'engagement' => 'Engagement Rate:<\/p>.+>([\d\.]+)<!-- -->%<\/p>',
            ];

            $html = limpaEspacosAcentuacao($html);

            #dd($html);

            $campos = [
                #'score' => null,
                'min' => null,
                'max' => null,
                #'engagement' => null,
                #'frequency' => null,
                #'length' => null
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
            #$campos['length'] = return_kmb_to_integer($campos['length']);

            dd($campos);

            return $campos;
        } catch (\Exception $e) {
            return ['erro' => 'Falha no scraping: ' . $e->getMessage()];
        }
    }


    public function getInscritos($youtube_id)
    {
        #$pairs  = $this->getWaybackSamples($youtube_id, 10); // amostra de 10
        #$result = $this->scrapeSubscribersFromSamples($pairs);
        #return $result;
    }

  
    




}
