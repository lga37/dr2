<?php

namespace App\Livewire;

use App\Traits\Comum;
use DOM\HtmlDocument;
use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class Tarefa3 extends Component
{


    use Comum;


    function getTipoTarefa(): string
    {
        return 't3';
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


    protected array $sessionPrefixes = ['t3_query', 't3_canais']; // ajuste como preferir



        public array $t3Charts = [];
        public array $vidiqMeans = [];


    public array $subsChart = []; // -> vai para o Blade



// thresholds
const POI_YPP     = 5_000;       // ponto de início de monetização
const POI_SILVER  = 100_000;
const POI_GOLD    = 1_000_000;
const POI_DIAMOND = 10_000_000;









public function mount()
{
    $this->selecionados = Session::get('t3_canais', []);
    $this->query        = Session::get('t3_query', '');

    // hoje (fim do período), como data pura YYYY-MM-DD
    $end = now()->startOfDay()->toDateString();

    $channels = [];

    foreach ($this->selecionados as $channelId => $raw) {
        // normaliza criação e inscritos
        $createdAt = \Carbon\Carbon::parse($raw['channelDt'] ?? null)->startOfDay()->toDateString();
        $subsNow   = (int) preg_replace('/\D+/', '', (string)($raw['channelSubs'] ?? 0));


        $pois = $this->buildPois($createdAt, $subsNow, $end);



 


// monetização média do vidIQ
$meanUSD = $this->vidiqMeans[$channelId] ?? null;
if ($meanUSD === null) {
    $earn = $this->estimateMonthlyEarningsFromVidiq($channelId, false);
    if (!empty($earn['ok']) && is_numeric($earn['mean'])) {
        $meanUSD = (float) $earn['mean'];
        $this->vidiqMeans[$channelId] = $meanUSD;
    }
}

// ponto de início da monetização = data do POI YPP (se existir), senão createdAt
$ypp = collect($pois)->firstWhere('label', 'like', 'YPP%');
$earnStart = $ypp['x'] ?? $createdAt;




// payload por canal
$channels[] = [
    'id'        => $channelId,
    'label'     => $raw['channelTitle'] ?? $channelId,
    'createdAt' => $createdAt,
    'subsNow'   => $subsNow,
    'pois'      => $pois,
    'meanUSD'   => $meanUSD,             // pode ser null
    'earnStart' => $earnStart,           // usado no gráfico de monetização
    'vidiqUrl'  => "https://vidiq.com/youtube-stats/channel/{$channelId}/",
];



    }

    $this->t3Charts = [
        'end'      => $end,
        'channels' => array_values($channels), // garante índice 0..N-1 (combina com subs-0/earn-0 no Blade)
    ];
}

        



private function buildPois(string $createdAtIso, int $subsNow, string $endIso): array
{
    $createdAt = \Carbon\Carbon::parse($createdAtIso)->startOfDay();
    $end       = \Carbon\Carbon::parse($endIso)->startOfDay();
    $days      = max(1, $createdAt->diffInDays($end));
    $rate      = $subsNow / $days; // inscritos/dia

    $make = function(int $targetSubs, string $label, string $color) use ($rate, $subsNow, $createdAt, $end) {
        if ($rate <= 0 || $targetSubs > $subsNow) return null;
        $d  = (int)ceil($targetSubs / $rate);
        $dt = $createdAt->copy()->addDays($d);
        if ($dt->gt($end)) return null;
        return [
            'x'     => $dt->toDateString(),
            'y'     => $targetSubs,
            'label' => $label,
            'color' => $color,
        ];
    };

    $pois = [];
    // origem (criação)
    $pois[] = ['x' => $createdAt->toDateString(), 'y' => 0, 'label' => 'Criação', 'color' => '#64748b'];

    // marcos
    foreach ([
        [self::POI_YPP,     'YPP (5k)',   '#16a34a'],
        [self::POI_SILVER,  'Silver 100k','#94a3b8'],
        [self::POI_GOLD,    'Gold 1M',    '#eab308'],
        [self::POI_DIAMOND, 'Diamond 10M','#60a5fa'],
    ] as [$subs, $label, $color]) {
        if ($poi = $make($subs, $label, $color)) $pois[] = $poi;
    }
    return $pois;
}




   

public function estimateMonthlyEarningsFromVidiq(string $channelId, bool $forceRefresh = false): array
{
    $channelId = trim($channelId);
    if ($channelId === '') return ['ok'=>false,'error'=>'channelId vazio'];

    $cacheKey = "earnings:vidiq:v2:{$channelId}";
    if (!$forceRefresh && Cache::has($cacheKey)) return Cache::get($cacheKey);

    $url = "https://vidiq.com/youtube-stats/channel/{$channelId}/";
    $ua  = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36';

    $out = [
        'ok'       => false,
        'url'      => $url,
        'currency' => 'USD',
        'min'      => null,
        'max'      => null,
        'mean'     => null,
        'raw'      => null,
        'error'    => null,
    ];

    try {
        $resp = Http::withHeaders([
            'User-Agent'      => $ua,
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ])->timeout(15)->retry(2, 400)->get($url);

        if (!$resp->successful()) {
            $out['error'] = "HTTP {$resp->status()}";
        } else {
            $html = $resp->body();

            // Procura bloco "Est. Monthly Earnings" e captura "$13K - $40K"
            $re = '~Est\.\s*Monthly\s*Earnings[\s\S]{0,500}?<span[^>]*>\s*\$([0-9.,]+[KMB]?)\s*[-–]\s*\$([0-9.,]+[KMB]?)\s*</span>~i';
            if (preg_match($re, $html, $m)) {
                $min = $this->parseUsdShort($m[1]);
                $max = $this->parseUsdShort($m[2]);
                if (is_numeric($min) && is_numeric($max)) {
                    $out['ok']   = true;
                    $out['min']  = $min;
                    $out['max']  = $max;
                    $out['mean'] = ($min + $max) / 2;
                    $out['raw']  = [$m[1], $m[2]];
                } else {
                    $out['error'] = 'Falha ao normalizar min/max';
                }
            } else {
                $out['error'] = 'Bloco "Est. Monthly Earnings" não encontrado';
            }
        }
    } catch (\Throwable $e) {
        $out['error'] = $e->getMessage();
    }

    Cache::put($cacheKey, $out, now()->addHours(12));
    return $out;
}

/** '13K' | '1.2M' | '61' → float */
protected function parseUsdShort(string $s): ?float
{
    $s = trim($s);
    $s = ltrim($s, '$');
    $s = str_replace(',', '', $s);
    if ($s === '') return null;

    if (preg_match('~^([0-9]*\.?[0-9]+)\s*([KMB])?$~i', $s, $m)) {
        $num = (float)$m[1];
        $suf = strtoupper($m[2] ?? '');
        return match ($suf) {
            'K' => $num * 1_000,
            'M' => $num * 1_000_000,
            'B' => $num * 1_000_000_000,
            default => $num,
        };
    }
    return null;
}



protected function buildSubsChartSimple(array $selecionados): array
{
    $out = [
        'start'  => null,
        'end'    => null,
        'series' => [],
    ];

    $globalStart = null;
    $globalEnd   = now()->startOfDay();

    foreach ($selecionados as $channelId => $raw) {
        $title     = $raw['channelTitle'] ?? $channelId;
        $subsNow   = (int)($raw['channelSubs'] ?? 0);
        $createdAt = \Carbon\Carbon::parse($raw['channelDt'])->startOfDay();
        $today     = now()->startOfDay();

        if (!$globalStart || $createdAt->lt($globalStart)) $globalStart = $createdAt;

        $days = max(1, $createdAt->diffInDays($today));
        $dailyRate = $subsNow / $days;

        // Queremos no máx. 60 pontos
        $maxPts = 60;
        $stepDays = max(1, intdiv($days, $maxPts)); // pulo em dias
        $points = [];
        for ($d = 0; $d <= $days; $d += $stepDays) {
            $dt = $createdAt->copy()->addDays($d);
            $y  = (int) round($d * $dailyRate);
            $points[] = ['x' => $dt->toDateString(), 'y' => $y];
        }
        // garante o último ponto exatamente em "hoje"
        if (end($points)['x'] !== $today->toDateString()) {
            $points[] = ['x' => $today->toDateString(), 'y' => $subsNow];
        }

        $out['series'][] = [
            'label'  => $title,
            'points' => $points,
        ];
    }

    $out['start'] = $globalStart ? $globalStart->toDateString() : null;
    $out['end']   = $globalEnd->toDateString();

    return $out;
}





    // thresholds (pontos de interesse)
    // const POI_YPP     = 4000;        // ajuste se quiser 5k
    // const POI_SILVER  = 100_000;
    // const POI_GOLD    = 1_000_000;
    // const POI_DIAMOND = 10_000_000;

    /**
     * Chame isto após carregar $this->selecionados (ex.: no mount ou no botão “Avaliar canais”).
     */
    public function montarGraficoInscritos(): void
    {
        if (!$this->selecionados) {
            $this->subsChart = [];
            return;
        }

        $globalStart = null;
        $globalEnd   = now()->startOfDay();

        $series = [];
        foreach ($this->selecionados as $channelId => $raw) {
            // dados base do canal
            $title     = (string)($raw['channelTitle'] ?? $channelId);
            $subsNow   = (int)($raw['channelSubs'] ?? 0);
            $createdAt = Carbon::parse($raw['channelDt'] ?? now())->startOfDay();

            $globalStart = $globalStart ? min($globalStart, $createdAt) : $createdAt;

            // modelo linear simples: 0 → subsNow entre createdAt → hoje
            $daysTotal = max(1, $createdAt->diffInDays($globalEnd));
            $rate      = $subsNow / $daysTotal;

            // pontos (x=date, y=subs) – ~1 ponto por mês (máx 24 p/ leve)
            $ticks = min(24, max(6, (int)ceil($daysTotal / 30)));
            $pts   = [];
            for ($i = 0; $i <= $ticks; $i++) {
                $d = $createdAt->copy()->addSeconds(
                    (int) floor(($i / $ticks) * $createdAt->diffInSeconds($globalEnd))
                );
                $subsAtD = (int) round($d->diffInDays($createdAt) * $rate);
                $pts[] = ['x' => $d->toDateString(), 'y' => $subsAtD];
            }

            // POIs por cruzamento do linear
            $pois = $this->buildPOIsSubs($createdAt, $globalEnd, $rate, $subsNow, [
                ['label' => 'Criação', 'subs' => 0,                  'color' => '#64748b'], // slate-500
                ['label' => 'YPP',     'subs' => self::POI_YPP,      'color' => '#16a34a'], // emerald-600
                ['label' => '100k',    'subs' => self::POI_SILVER,   'color' => '#94a3b8'], // slate-400
                ['label' => '1M',      'subs' => self::POI_GOLD,     'color' => '#eab308'], // amber-500
                ['label' => '10M',     'subs' => self::POI_DIAMOND,  'color' => '#60a5fa'], // sky-400
            ]);

            $series[] = [
                'id'     => $channelId,
                'label'  => $title,
                'points' => $pts,
                'pois'   => $pois,
            ];
        }

        $this->subsChart = [
            'start'  => $globalStart?->toDateString(),
            'end'    => $globalEnd->toDateString(),
            'series' => $series,
        ];
    }

    /** Calcula datas dos POIs em cima do modelo linear. */
    protected function buildPOIsSubs(
        Carbon $createdAt,
        Carbon $today,
        float $ratePerDay,
        int $subsNow,
        array $defs
    ): array {
        $out = [];
        foreach ($defs as $d) {
            $subs = (int)$d['subs'];
            if ($subs <= 0) {
                $out[] = [
                    'x'     => $createdAt->toDateString(),
                    'y'     => 0,
                    'label' => $d['label'],
                    'color' => $d['color'],
                ];
                continue;
            }
            // só marca se o degrau foi alcançado
            if ($ratePerDay > 0 && $subs <= $subsNow) {
                $days = (int)ceil($subs / $ratePerDay);
                $dt   = $createdAt->copy()->addDays($days);
                if ($dt->lte($today)) {
                    $out[] = [
                        'x'     => $dt->toDateString(),
                        'y'     => $subs,
                        'label' => $d['label'],
                        'color' => $d['color'],
                    ];
                }
            }
        }
        return $out;
    }











    public function validarTarefa3()
    {
        // carrega seleção e zera memória local
        $this->selecionados = Session::get('t3_canais', $this->selecionados);
        $this->mostrarFeedback = true;


        if (!$this->maisPolarizado) {
            $this->msg('Vc deve selecionar um registro');
            return;
        }
        // reset do cache desta tela (opcional)
        Session::forget('t3_videos');
        $sessVideos = [];
        $tarefa = $this->getTarefa('T2');
        #$storage = app(YoutubeStorage::class);

        foreach ($this->selecionados as $canalId => $raw) {

            $q = $raw['busca'] ?? '[erro]';
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
                ];

                #dump($vd);
                $videoBD = $this->upsertVideo($vd, $canalBD, $buscaBD);
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
        Session::put('t3_videos', $sessVideos);

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

        Session::put('t3_result', [
            'selecionados'      => $this->selecionados,
            'polariz_media'         => $this->polarizMediaArray,
            'mais_polarizado'       => $this->maisPolarizado,
            'mais_polarizado_real'  => $this->maisPolarizadoReal,
            'acertou'           => $this->acertou,
            'videos'       => $this->videos_dos_canais,
            'buscas'            => $this->buscas,

        ]);
    }





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


    ########################################### Trait canais

    public function clearSelecionados(): void
    {
        // 1) Resetar TODAS as props públicas para o default
        $this->reset();             // volta ao estado declarado na classe
        $this->resetErrorBag();     // (se usa validação)
        $this->resetValidation();   // (se usa validação)

        // 2) Limpar SESSIONS “da tela” por prefixo (não mexe em auth)
        $this->forgetSessionByPrefix($this->sessionPrefixes);

        // 3) Se usa paginação, zere a página atual
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function forgetSessionByPrefix(array $prefixes): void
    {
        $allKeys = array_keys(Session::all());

        $toForget = array_values(array_filter($allKeys, function ($key) use ($prefixes) {
            return Str::startsWith($key, $prefixes);
        }));

        if (!empty($toForget)) {
            Session::forget($toForget);
        }
    }

    public function pesquisarCanais(): void
    {
        $this->query = $this->normalizeQuery($this->query);

        if (mb_strlen($this->query) < 2) {
            $this->msg('Digite pelo menos 2 caracteres para pesquisar.', 'warn');
            return;
        }

        $this->getCanais();
    }

    protected function persistSelecionados(): void
    {

        // dump($this->selecionados);
        $this->selecionados = array_filter(
            $this->selecionados,
            static fn($value, $key) => is_string($key) && is_array($value) && !empty($value),
            ARRAY_FILTER_USE_BOTH
        );

        // dump($this->selecionados);

        if (empty($this->selecionados)) {
            Session::forget('t3_canais');
        } else {
            Session::put('t3_canais', $this->selecionados);
        }
    }



    public function add(string $canalId): void
    {
        if (!isset($this->selecionados[$canalId])) {
            $tem = false;
            #dd($this->buscas);
            foreach ($this->buscas as $reg_canal) {
                if ($reg_canal['channelId'] == $canalId) {
                    $data = $reg_canal;
                    $tem = true;
                    break;
                }
            }
            if (!$tem) {
                $dadosCanal = $this->getCanaisDetailsByListCanaisIds([$canalId]);
                $data = $dadosCanal[$canalId] ?? null;   // pega só o registro do canal
                if (!$data) {
                    $this->msg('Não foi possível obter os detalhes do canal.', 'error');
                    return;
                }
                $data['q'] = '--';
            }
            if (is_array($data) && !empty($data)) {

                if ($reg_canal['channelSubs'] >= 5000 && $reg_canal['channelVideos'] <= 1000) {
                    $this->selecionados[$canalId] = $data;

                    $this->persistSelecionados();
                    $this->msg('Registro ' . $canalId . ' adicionado corretamente.', 'success');
                } else {
                    $this->msg('Registro ' . $canalId . ' deve ter +5K incritos e -1K videos para essa tarefa.', 'warn');
                }
            }
        } else {
            $this->msg('Nao adicionado pois já consta', 'error');
        }
        #$this->reset('addInput'); // limpa o input na view

    }

    public function addCanalByInput(): void
    {
        $input = trim($this->addInput);

        if ($input === '')
            return;

        $id = $this->parseCanalIdentifier($input);

        if ($id && !in_array($id, $this->selecionados, true)) {
            #dd($id);
            $this->add($id);
        } else {
            $this->msg('Canal com erro - nao adicionado', 'error');
        }
        $this->reset('addInput'); // limpa o input na view

    }


    protected function parseCanalIdentifier(string $input): bool|string
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
            $handle = '@' . $m[1];
            $id = $this->pegaChannelIdViaHandle($handle);
            return $id;
            #dd($id);

        }
        // fallback: tentar achar pelo nome
        return false;
    }


    function pegaChannelIdViaHandle(string $handle)
    {

        #dd($handle);
        $apiKey = env('YOUTUBE_API_KEY');
        $params = [
            'key'             => $apiKey,
            'part'            => 'id',
            'forHandle'       => $handle,
        ];

        $url = 'https://www.googleapis.com/youtube/v3/channels?' . http_build_query($params);
        $res = file_get_contents($url);
        if ($res) {
            $json = json_decode($res, true) ?: [];
            # dd($json);
            $id = $json['items'][0]['id'] ?? false;
            return $id;
        } else {
            $this->msg('Erro ao resolver @handle na API', 'error');
            return false;
        }
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

    public function clearChannelsSearchCache(): void
    {
        $q = trim((string) $this->query);
        if ($q !== '') {
            Cache::forget('yt:search:channels:v1:' . md5(mb_strtolower($q)));
        }
    }





    #1  ###################################################################################
    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.tarefa3');
    }



    protected function getMonets($canalId)
    {

        $url_vidiq = "https://vidiq.com/youtube-stats/channel/$canalId/";

        #<div class="rounded-lg p-3 outline outline-offset-[-1px] outline-zinc-800"><p class="mb-1 text-xs font-semibold text-vidiq-body-gray md:text-[15px]">Est. Monthly Earnings</p><div class="flex items-center justify-start"><span class="text-xl font-semibold md:text-[28px]">$13K - $40K</span></div></div>


        $url_socialblade = "https://socialblade.com/youtube/channel/$canalId";

        #<div class="my-auto"><h2 class="flex justify-center items-center text-3xl font-bold text-nowrap">$4 - $61</h2><h3 class="text-sm opacity-70 text-nowrap">Monthly Estimated Earnings</h3></div>



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


            #'min_max'    => 'Est\.\s*Monthly\s*Earnings[\s\S]*?<span[^>]*>\s*\$([\d.,]+[KMB]?)\s*[-–]\s*\$([\d.,]+[KMB]?)\s*<\/span>',



            #$html = limpaEspacosAcentuacao($html);

            #dd($html);




            return $campos;
        } catch (\Exception $e) {
            return ['erro' => 'Falha no scraping: ' . $e->getMessage()];
        }
    }
}
