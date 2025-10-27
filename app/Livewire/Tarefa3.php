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
    public ?string $maisEconomizado = null;
    public array $polarizMediaArray = [];
    public ?string $maisEconomizadoReal = null;
    public ?bool $acertou = null;
    public string $feedback = '';       // textarea
    public bool $mostrarAvaliacao = true;

    public bool $mostrarFeedback = false;


    protected array $sessionPrefixes = ['t3_query', 't3_canais']; // ajuste como preferir


    public array $subsChart = []; // -> vai para o Blade


    public function mount()
    {
        $this->selecionados = Session::get('t3_canais', []);
        $this->query        = Session::get('t3_query', '');

        $today = Carbon::createFromFormat('Y-m-d', '2025-10-01')->startOfDay();

        foreach ($this->selecionados as $idx => $row) {
            // if (isset($row['monetAvgUsd'], $row['minutagemTotal'])) {
            //     continue;
            // }

            $canalId    = $row['channelId']      ?? null;
            $createdIso = $row['channelDt']      ?? null;
            $subsHoje   = (int)($row['channelSubs'] ?? 0);
            if (!$canalId || !$createdIso) continue;

            $start = Carbon::parse($createdIso)->startOfDay();
            if ($start->greaterThan($today)) $start = (clone $today);

            // === cálculos/consultas ===
            $monetAvgUsd = $this->getVidIqMonthlyAvgUsd($canalId) ?? 0.0;

            $yt         = $this->ytFetchChannelDurations($canalId);
            $minTotSec  = $yt['total_seconds'] ?? 0;
            $minTotFmt  = $this->fmtDuration($minTotSec);
            $videosCont = $yt['count'] ?? 0;

            [$dt5000, $diasMonetizados] = $this->calcDt5000EDias($start, $subsHoje, $today);

            // === acrescenta APENAS os campos solicitados ao selecionado ===
            $this->selecionados[$idx]['monetAvgUsd']       = $monetAvgUsd;
            $this->selecionados[$idx]['minutagemTotal']    = $minTotSec;
            $this->selecionados[$idx]['minutagemTotalFmt'] = $minTotFmt;
            $this->selecionados[$idx]['videos']            = $videosCont;
            $this->selecionados[$idx]['dt5000']            = $dt5000?->toDateString();
            $this->selecionados[$idx]['diasMonetizados']   = $diasMonetizados;
        }

        // persiste de volta na sessão
        Session::put('t3_canais', $this->selecionados);
    }


    private function ytFetchChannelDurations(string $channelId): array
    {
        $apiKey = config('services.youtube.key', env('YOUTUBE_API_KEY'));
        if (!$apiKey) return ['count' => 0, 'total_seconds' => 0];

        // Cache por 6h por canal
        $cacheKey = "yt:uploads:durations:{$channelId}";
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($channelId, $apiKey) {

            // 1) descobrir a playlist de uploads do canal
            $uploadsId = $this->ytGetUploadsPlaylistId($channelId, $apiKey);
            if (!$uploadsId) return ['count' => 0, 'total_seconds' => 0];

            // 2) listar TODOS os videoIds da playlist (pagina em 50)
            $videoIds = $this->ytListAllPlaylistVideoIds($uploadsId, $apiKey);

            if (empty($videoIds)) return ['count' => 0, 'total_seconds' => 0];

            // 3) buscar detalhes em lotes de 50 (durations + publishedAt)
            $totalSec = 0;
            $count    = 0;

            foreach (array_chunk($videoIds, 50) as $chunk) {
                $ids = implode(',', $chunk);
                $res = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/videos', [
                    'key'   => $apiKey,
                    'part'  => 'contentDetails,snippet',
                    'id'    => $ids,
                    'maxResults' => 50,
                ]);

                if (!$res->ok()) continue;

                foreach (($res['items'] ?? []) as $it) {
                    $iso = $it['contentDetails']['duration'] ?? null;   // ex.: PT12M34S
                    if (!$iso) continue;

                    $sec = $this->iso8601ToSeconds($iso);
                    $totalSec += $sec;
                    $count++;
                }
            }

            return [
                'count'         => $count,
                'total_seconds' => $totalSec,
            ];
        });
    }

    private function ytGetUploadsPlaylistId(string $channelId, string $apiKey): ?string
    {
        $res = Http::timeout(15)->get('https://www.googleapis.com/youtube/v3/channels', [
            'key'  => $apiKey,
            'part' => 'contentDetails',
            'id'   => $channelId,
            'maxResults' => 1,
        ]);
        if (!$res->ok()) return null;

        return $res['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
    }

    private function ytListAllPlaylistVideoIds(string $playlistId, string $apiKey): array
    {
        $ids = [];
        $page = null;

        do {
            $res = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/playlistItems', [
                'key'        => $apiKey,
                'part'       => 'contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken'  => $page,
            ]);
            if (!$res->ok()) break;

            foreach (($res['items'] ?? []) as $it) {
                $vid = $it['contentDetails']['videoId'] ?? null;
                if ($vid) $ids[] = $vid;
            }

            $page = $res['nextPageToken'] ?? null;
        } while ($page);

        return $ids;
    }


    private function calcDt5000EDias(Carbon $dataInicio, int $inscritosHoje, Carbon $hoje): array
    {
        if ($inscritosHoje <= 0) return [null, 0];

        $minutosTot = $dataInicio->diffInMinutes($hoje);       // precisão boa
        $proporcao  = 5000 / max(1, $inscritosHoje);
        $minAte5k   = (int) round($minutosTot * $proporcao);

        $dt5000 = (clone $dataInicio)->addMinutes($minAte5k);
        $dias   = $dt5000->lessThan($hoje) ? $dt5000->diffInDays($hoje) : 0;
        $dias = round($dias, 2);

        return [$dt5000, $dias];
    }


    private function fmtDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        if ($h > 0)  return sprintf('%d h %02d min', $h, $m);
        return sprintf('%d min', $m);
    }


    private function getVidIqMonthlyAvgUsd(string $channelId): ?float
    {
        $url = "https://vidiq.com/youtube-stats/channel/{$channelId}/";

        try {
            $res = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(20)->get($url);

            if (!$res->ok()) return null;

            $html = $res->body();

            // 1) DOM + XPath (mais seguro contra variações)
            if ($val = $this->parseVidiqWithDom($html)) {
                return $val;
            }

            // 2) Regex mais “estruturada” (fecha </p> e pega o <span> da mesma seção)
            if (preg_match('~Est\.\s*Monthly\s*Earnings\s*</p>\s*<div[^>]*>\s*<span[^>]*>([^<]+)</span>~i', $html, $m)) {
                if ($avg = $this->parseRangeToAvgUsd(trim($m[1]))) return $avg;
            }

            // 3) Regex ampla (qualquer <span> após o texto-chave)
            if (preg_match('~Est\.\s*Monthly\s*Earnings.*?<span[^>]*>([^<]+)</span>~is', $html, $m2)) {
                if ($avg = $this->parseRangeToAvgUsd(trim($m2[1]))) return $avg;
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseVidiqWithDom(string $html): ?float
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // acha o <p> cujo texto contém "Est. Monthly Earnings"
        $nodes = $xpath->query("//p[contains(normalize-space(.), 'Est. Monthly Earnings')]");
        if (!$nodes || $nodes->length === 0) return null;

        // pega o primeiro <span> visível logo após (irmão/descendente na mesma seção)
        $p = $nodes->item(0);
        // sobe ao container
        $container = $p->parentNode;
        if (!$container) return null;

        // tenta um span dentro do container
        $spanNodes = (new \DOMXPath($dom))->query(".//span", $container);
        foreach ($spanNodes as $sp) {
            $text = trim($sp->textContent ?? '');
            if ($text !== '') {
                if ($avg = $this->parseRangeToAvgUsd($text)) {
                    return $avg;
                }
            }
        }

        // fallback: próximo span no DOM depois do <p>
        $allSpans = (new \DOMXPath($dom))->query("//span");
        $foundP = false;
        foreach ($dom->getElementsByTagName('p') as $pnode) {
            if (strpos($pnode->textContent ?? '', 'Est. Monthly Earnings') !== false) {
                $foundP = true;
                break;
            }
        }
        if ($foundP && $allSpans && $allSpans->length > 0) {
            foreach ($allSpans as $sp) {
                $text = trim($sp->textContent ?? '');
                if ($text !== '' && $this->parseRangeToAvgUsd($text)) {
                    return $this->parseRangeToAvgUsd($text);
                }
            }
        }

        return null;
    }

    /** Converte "$13K - $40K" / "$44 - $132" / "1.2M" em média USD (float) */
    private function parseRangeToAvgUsd(string $rangeText): ?float
    {
        // normaliza entidades e espaços
        $rangeText = html_entity_decode($rangeText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rangeText = preg_replace('/[^\S\r\n]+/u', ' ', $rangeText); // colapsa espaços

        // formato "min - max"
        if (preg_match('~\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?\s*-\s*\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?~', $rangeText, $mm)) {
            $min = $this->toNumber($mm[1], $mm[2] ?? null);
            $max = $this->toNumber($mm[3], $mm[4] ?? null);
            if ($min !== null && $max !== null) {
                return ($min + $max) / 2.0;
            }
        }

        // formato único "$1.2K"
        if (preg_match('~\$?\s*([0-9][0-9\.,]*)\s*([KkMm])?~', $rangeText, $ms)) {
            return $this->toNumber($ms[1], $ms[2] ?? null);
        }

        return null;
    }

    private function toNumber(string $num, ?string $suffix): ?float
    {
        $n = str_replace([' ', ','], ['', '.'], $num);
        if (!is_numeric($n)) return null;

        $v = (float)$n;
        if ($suffix) {
            $s = strtolower($suffix);
            if ($s === 'k') $v *= 1_000;
            if ($s === 'm') $v *= 1_000_000;
        }
        return $v;
    }


    ###########################################################
    ###########################################################

    public function validarTarefa3()
    {
        // carrega seleção e zera memória local
        $this->selecionados = Session::get('t3_canais', $this->selecionados);
        $this->mostrarFeedback = true;


        if (!$this->maisEconomizado) {
            $this->msg('Vc deve selecionar um registro');
            return;
        }
        // reset do cache desta tela (opcional)
        Session::forget('t3_videos');
        $sessVideos = [];

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
        $arrayMaisEconomizados = $this->pickMaisPolariz($this->polarizMediaArray);
        $mais_Economizado_posit = $arrayMaisEconomizados['mais_Economizado_posit']['id'];
        $mais_Economizado_negat = $arrayMaisEconomizados['mais_Economizado_negat']['id'];
        $this->maisEconomizadoReal = $arrayMaisEconomizados['mais_Economizado']['id'];

        //  if ($bestPosId !== null) {
        //     $out['mais_Economizado_posit'] = ['id' => $bestPosId, 'score' => $bestPosVal];
        // }
        // if ($bestNegId !== null) {
        //     $out['mais_Economizado_negat'] = ['id' => $bestNegId, 'score' => $bestNegVal];
        // }
        // if ($bestAbsId !== null) {
        //     $out['mais_Economizado'] = ['id' => $bestAbsId, 'score' => $bestAbsScore];
        // }


        $this->acertou = ($this->maisEconomizadoReal && $this->maisEconomizado)
            ? $this->maisEconomizadoReal === $this->maisEconomizado
            : false;

        #dd($this->acertou);

        Session::put('t3_result', [
            'selecionados'      => $this->selecionados,
            'polariz_media'         => $this->polarizMediaArray,
            'mais_Economizado'       => $this->maisEconomizado,
            'mais_Economizado_real'  => $this->maisEconomizadoReal,
            'acertou'           => $this->acertou,
            'videos'       => $this->videos_dos_canais,
            'buscas'            => $this->buscas,

        ]);
    }




    public function escolherMaisEconomizado(string $canalId): void
    {
        // aceita só A/B e evita qualquer reset inesperado
        // if ($canal === 'A' || $canal === 'B') {
        //     $this->maisEconomizado = $canal;
        // }
        if (!isset($this->selecionados[$canalId])) return;

        $this->maisEconomizado = $canalId;
        Session::put('tarefa3_mais_economizado', $canalId);

        // limpa resultado anterior se o usuário mudar de ideia
        $this->acertou = null;
    }



    // Só libera a renderização do bloco
    public function avaliarCanais(): void
    {
        // Se quiser, dá pra exigir pelo menos 2 vídeos:
        if (count($this->selecionados) < 2)
            return;

        $this->mostrarAvaliacao = true;
        $this->videos_dos_canais  = [];
        $this->maisEconomizado = null;
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

        #dump($this->selecionados);

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
