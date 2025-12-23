<?php

namespace App\Traits;

use Log;
use App\Models\Busca;
use App\Models\Canal;
use App\Models\Video;
use App\Models\Tarefa;
use App\Models\Comentario;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use App\Services\YoutubeStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;


trait Comum
{



    #[Url()]
    public string $query = '';

    public ?Tarefa $tarefa = null;
    private array $cache = [];




    protected function ISO8601ToSeconds($duration): ?int
    {
        if ($duration === null || $duration === '') {
            return null;
        }

        // 1) Já em segundos (int ou string numérica)
        if (is_int($duration) || ctype_digit((string) $duration)) {
            return (int) $duration;
        }

        $duration = trim((string) $duration);

        // 2) Formato HH:MM:SS ou MM:SS
        if (preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{2})$/', $duration, $m)) {
            $h = (int) ($m[1] ?? 0);
            $i = (int) $m[2];
            $s = (int) $m[3];
            return $h * 3600 + $i * 60 + $s;
        }

        // 3) ISO-8601 (PT#H#M#S, P#DT#H#M#S, etc.)
        try {
            $iv = new \DateInterval($duration);
            // somar também dias (e opcionalmente semanas/meses/anos, se usar)
            $days = property_exists($iv, 'days') && $iv->days !== false ? (int) $iv->days : (int) $iv->d;
            return ($days * 86400) + ($iv->h * 3600) + ($iv->i * 60) + $iv->s;
        } catch (\Throwable $e) {
            // formato desconhecido
            return null;
        }
    }



    private function fmtDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        if ($h > 0)  return sprintf('%d h %02d min', $h, $m);
        return sprintf('%d min', $m);
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


    // Normalizador simples pra não sujar a sessão
    private function normalizeQuery(string $value): string
    {


        $value = trim(preg_replace('/\s+/u', ' ', $value)); // 1+ espaços -> 1 espaço
        $value = strip_tags($value);
        // limita tamanho (evita URL gigante e payload desnecessário)
        $value = mb_strimwidth($value, 0, 30, '');

        $this->upsertBusca($value);
        return $value;
    }

    // Dispara quando a prop "query" muda (graças ao wire:model.*)
    public function updatedQuery($value): void
    {
        $tipo_tarefa = $this->getTipoTarefa();

        $this->query = $this->normalizeQuery($value);

        Session::put($tipo_tarefa . '_query', $this->query);

        // Se quiser buscar a cada mudança, faça com um gate simples:
        if (mb_strlen($this->query) >= 2) {
            // cuidado para não spammar a API:
            if ($tipo_tarefa == 't1') {
                $videos = $this->getVideos();
                Session::put($tipo_tarefa . '_videos', $videos);
            } else {
                $canais = $this->getCanais();
                #dd($canais);


                // Trait Comum (onde monta a grid)
                #$key = $tipo_tarefa . '_canais:' . session()->getId();      // chave por sessão do usuário
                #Cache::put($key, $canais, now()->addHours(4)); // guarda a GRID no cache
                Session::put($tipo_tarefa . '_buscas', $canais);           // guarda só o ponteiro


                #Session::put($tipo_tarefa . '_canais', $canais);
                #dd(session()->all());

            }
        }
    }

    // Caso prefira acionar só no botão "Pesquisar":
    public function pesquisar(): void
    {
        $this->query = $this->normalizeQuery($this->query);
        $tipo = $this->getTipoTarefa();


        if (mb_strlen($this->query) < 2) {
            $this->msg('Digite pelo menos 2 caracteres para pesquisar.', 'warn');
            return;
        }


        $forceRefresh = false;
        $this->buscas = $this->getVideos($forceRefresh);
        Session::put($tipo . '_query', $this->query);
    }

    private function squashSpaces(?string $text): ?string
    {
        if ($text === null) return null;

        // remove HTML, normaliza entidades (&nbsp;), etc
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // troca NBSP (0xA0) por espaço normal
        $text = preg_replace('/\x{00A0}/u', ' ', $text);

        // qualquer whitespace (quebra de linha, tab, múltiplos espaços) -> 1 espaço
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    protected function parseBrandingKeywords($raw): array
    {
        // 1) Garante string
        $raw = is_array($raw) ? implode(' ', $raw) : (string) $raw;

        // 2) Normaliza aspas “curly” para aspas simples
        $raw = str_replace(['“', '”', '„', '«', '»'], '"', $raw);

        // 3) Converte separadores comuns em espaço
        $raw = str_replace([',', ';', '|'], ' ', $raw);

        // 4) Frases entre aspas OU tokens sem espaço
        preg_match_all('/"([^"]+)"|(\S+)/u', $raw, $m);

        $kw = [];
        foreach ($m[0] as $i => $full) {
            $t = $m[1][$i] !== '' ? $m[1][$i] : $m[2][$i];
            $t = trim($t, " \t\n\r\0\x0B\"'");
            if ($t !== '') $kw[] = $t;
        }

        // 5) Remove duplicatas preservando ordem
        return array_values(array_unique($kw));
    }


    protected function toDate(?string $v): ?string
    {
        if (!$v) return null;
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
    protected function toDateTime(?string $v): ?string
    {
        if (!$v) return null;
        try {
            return Carbon::parse($v)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
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


    ################################################# atencao - ta vinculado a t3
    protected function persistSelecionados(): void
    {

        $this->selecionados = array_filter(
            $this->selecionados,
            static fn($value, $key) => is_string($key) && is_array($value) && !empty($value),
            ARRAY_FILTER_USE_BOTH
        );

        #dump($this->selecionados);

        $t = $this->getTipoTarefa();
        #dd($t);

        if ($t == 't1') {
            $nome_sess = "sel_videos";
        } else {
            $nome_sess = $t . "_canais";
        }

        if (empty($this->selecionados)) {
            Session::forget($nome_sess);
        } else {
            Session::put($nome_sess, $this->selecionados);
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
                $this->selecionados[$canalId] = $data;
                #dd($this->selecionados);
                $this->persistSelecionados();
                $this->msg('Registro ' . $canalId . ' adicionado corretamente', 'success');
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
            $t = $this->getTipoTarefa();
            if ($t == 't4') {
                $monet = $this->getVidIqMonthlyAvgUsd($id);
                if (empty($monet)) {
                    $this->msg('Monetizacao inexistente', 'error');
                    return;
                }
                $this->selecionados[$id]['monete'] = $monet;
            }
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



    ########## atencao pus na comum - updated query chama getVideos
    ####################################################################################
    public function getVideos(bool $forceRefresh = false): array
    {
        $q = trim((string) $this->query);
        if ($q === '') {
            return $this->buscas;
        }

        $cacheKey = 'yt:search:v2:' . md5(mb_strtolower($q));
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return $this->buscas = Cache::get($cacheKey);
        }

        $apiKey = env('YOUTUBE_API_KEY');

        $url = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
            'key'        => $apiKey,
            'part'       => 'snippet',
            'q'          => $q,
            'type'       => 'video',
            'maxResults' => 50,
        ]);
        Log::info('YT API:' . __CLASS__ . ' / ' . __FUNCTION__ . '()', ['url' => $url]);

        $resp  = file_get_contents($url);
        $json  = json_decode($resp ?? '[]', true);
        $items = collect($json['items'] ?? [])->values()->all();

        $result = $items ? $this->hydrateVideosFromSearchResults($items) : [];

        // 👉 indexa por videoId e adiciona a query 'q' em cada registro
        // $result = collect($result)
        //     ->filter(fn($row) => !empty($row['videoId'])) // por segurança
        //     ->map(fn($row) => $row + ['q' => $q])
        //     ->keyBy('videoId')
        //     ->toArray();

        $tipo_tarefa = $this->getTipoTarefa();

        $result = collect($result)
            ->filter(fn($row) => !empty($row['videoId'])) // segurança básica
            ->when(
                $tipo_tarefa == 't1',
                fn($col) => $col->filter(fn($row) =>
                    isset($row['commentCount']) && $row['commentCount'] <= 600
                )
            )
            ->map(fn($row) => $row + ['q' => $q])
            ->keyBy('videoId')
            ->toArray();

            #dd($result);

        Cache::put($cacheKey, $result, now()->addDay());

        #dd($result);

        return $this->buscas = $result;
    }

    # pega os canais via query
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
            if (!$chId || empty($detailsById[$chId])) 
                continue;

            $row = $detailsById[$chId];
            $row['q'] = $q;           // anota a query usada
            #$out[] = $row;
            if($this->getTipoTarefa() == 't3'){
                #dd('ff');
                if($row['channelCountry']== 'BR'){
                    $out[] = $row;
                }
            } else {
                $out[] = $row;
            }
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

    ########################################### fIM Trait canais



    #comum
    public function removeSelecionado33333333333333333(string $registroId): void
    {
        unset($this->selecionados[$registroId]);
        $this->persistSelecionados();
    }


public function removeSelecionado(string $registroId): void
{
    unset($this->selecionados[$registroId]);

    // limpa estados derivados (tabela / médias / gráfico)
    if (property_exists($this, 'samples'))      unset($this->samples[$registroId]);
    if (property_exists($this, 'comentarios'))  unset($this->comentarios[$registroId]);
    if (property_exists($this, 'toxMediaArray'))unset($this->toxMediaArray[$registroId]);


    // limpa caches de sessão relacionados (T1)
    $sess = session('t1_comentarios', []);
    unset($sess[$registroId]);
    session()->put('t1_comentarios', $sess);

    // persiste selecionados (sel_videos)
    $this->persistSelecionados();

    // opcional: se quiser forçar sumir o bloco de avaliação quando ficar <2
    if (count($this->selecionados) < 2) {
        $this->mostrarAvaliacao = false;
        $this->feedback = '';
    }
}



    public function getAllVideos(
            string $channelId,
            ?string $channelCreatedAt = null,
            int $max = 100,
            int $maxPages = 5,
            int $page = 1,
            int $totalInformado = 0,
            array $acc = [],
            ?string $pageToken = null
        ) {
        $key = env('YOUTUBE_API_KEY');

        $url = "https://www.googleapis.com/youtube/v3/search"
            . "?key={$key}"
            . "&channelId={$channelId}"
            . "&part=snippet"
            . "&order=date"
            . "&type=video"
            . "&maxResults=50";

        if ($page > 1 && $pageToken) {
            $url .= "&pageToken={$pageToken}";
        }

        $resp = Http::timeout(15)->get($url);
        if ($resp->failed()) {
            return $acc;
        }

        $json  = $resp->json();
        $items = $json['items'] ?? [];

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? [];
            $videoId = data_get($item, 'id.videoId');
            if (!$videoId) continue;

            $title = (string) ($snippet['title'] ?? '');
            $desc  = (string) ($snippet['description'] ?? '');

            #$nlp1 = 100 * $this->setTox($title);
            #$nlp2 = 100 * $this->setTox($desc);

            $acc[] = [
                'videoId'      => $videoId,
                'videoTitle'   => $title,
                'videoDesc'    => $desc,
                'videoDt'      => $snippet['publishedAt'] ?? null,
                'channelId'    => $snippet['channelId'] ?? '',
                'channelTitle' => $snippet['channelTitle'] ?? '',
                'videoThumb'   => data_get($snippet, 'thumbnails.medium.url'),
                #'nlp1'         => is_numeric($nlp1) ? (float)$nlp1 : null,
                #'nlp2'         => is_numeric($nlp2) ? (float)$nlp2 : null,
            ];

            if (count($acc) >= $max) 
                break;
        }

        $nextToken = $json['nextPageToken'] ?? null;
        $temMais   = $nextToken && (count($acc) < $max) && ($page < $maxPages);

        if ($temMais) {
            return $this->getAllVideos(
                $channelId,
                $channelCreatedAt,
                $max,
                $maxPages,
                $page + 1,
                $totalInformado,
                $acc,
                $nextToken
            );
        }

        usort($acc, fn($a, $b) => strtotime($b['videoDt'] ?? '1970-01-01') <=> strtotime($a['videoDt'] ?? '1970-01-01'));

    return array_slice($acc, 0, $max);
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

    protected function msg(string $txt, string $type = 'error'): void
    {
        $types = ['error', 'warn', 'info', 'success'];
        if (!in_array($type, $types, true)) {
            $type = 'info';
        }

        session()->flash('alert', [
            'type' => $type,
            'text' => $txt,
        ]);
    }


    private function tokenizaKeywordsMesmoComEspaco($rawKW): array
    {
        $rawKW = is_array($rawKW) ? implode(' ', $rawKW) : (string) $rawKW;
        // 2) Normaliza aspas “curly” -> "
        $rawKW = str_replace(['“', '”', '„', '«', '»'], '"', $rawKW);
        // 3) Troca possíveis separadores por espaço
        $rawKW = str_replace([',', ';', '|'], ' ', $rawKW);
        // 4) Tokeniza: frases entre aspas ou tokens sem espaço
        preg_match_all('/"([^"]+)"|(\S+)/u', $rawKW, $m);
        // 5) Consolida tokens (grupo 1 = frase entre aspas, grupo 2 = token solto)
        $keywords = [];
        foreach ($m[0] as $i => $full) {
            $t = $m[1][$i] !== '' ? $m[1][$i] : $m[2][$i];
            $t = trim($t, " \t\n\r\0\x0B\"'"); // remove aspas/sobras nas pontas
            if ($t !== '') {
                $keywords[] = $t;
            }
        }

        return $keywords;
    }



    /** Retorna a tarefa aberta do tipo (cria se não existir). */
    public function current(): Tarefa
    {

        #$k = $this->key($tipo);

        $tipo = $this->getTipoTarefa();

        $k = $tipo;

        // 1) cache em memória do request
        if (isset($this->cache[$k])) {
            return $this->cache[$k];
        }

        // 2) sessão -> carrega por id e valida status/tipo
        if ($id = Session::get($k)) {
            $t = Tarefa::whereKey($id)
                ->where('tipo', $tipo)
                ->where('status', 0)
                ->first();

            if ($t) {
                return $this->cache[$k] = $t;
            }

            // id inválido/fechado -> limpa sessão
            Session::forget($k);
        }

        // 3) última tarefa aberta do usuário
        $t = Tarefa::where('user_id', Auth::id())
            ->where('tipo', $tipo)
            ->where('status', 0)
            ->latest('id')
            ->first();

        // 4) se não há, cria uma nova
        if (!$t) {
            $t = Tarefa::create([
                'user_id' => Auth::id(),   // garanta que a rota está sob 'auth'
                'tipo'    => $tipo,
                'status'  => 0,
            ]);
        }

        // 5) persiste apenas o id na sessão e guarda no cache local
        Session::put($k, $t->id);
        return $this->cache[$k] = $t;
    }

    /** Fecha a tarefa aberta do tipo e remove o id da sessão */
    public function close(string $tipo, array $extra = []): Tarefa
    {
        $t = $this->current();
        $t->status = 1;

        if (array_key_exists('feedback', $extra)) {
            $t->feedback = $extra['feedback'];
        }

        $t->finished_at = $extra['finished_at'] ?? now();
        $t->save();

        #$k = $this->key($tipo);
        $k = $this->getTipoTarefa();

        Session::forget($k);
        unset($this->cache[$k]);

        return $t;
    }

    /** Apenas limpa o id da sessão (sem fechar a tarefa) */
    public function forget(string $tipo): void
    {
        #$k = $this->key($tipo);
        $k = $this->getTipoTarefa();

        Session::forget($k);
        unset($this->cache[$k]);
    }

    /** Retorna o id da tarefa aberta do tipo */
    public function getTarefaId(): int
    {

        return $this->current()->id;
    }

    /** Retorna o model da tarefa aberta do tipo */
    public function getTarefa(): Tarefa
    {
        return $this->current();
    }

    #####################################################

    #pode vir mais de 50 videos ele chunka por 50
    # 550 video_ids => 550 video_details --------- chunk 50 - 4 parts API YT



    public function getVideoDetailsByListVideoIds(array $ids): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) return [];

        $out = [];

        foreach (array_chunk($ids, 50) as $pack) {
            $params = [
                'key'  => $apiKey,
                'id'   => implode(',', $pack),
                'part' => 'snippet,statistics,contentDetails',
            ];
            $url = 'https://www.googleapis.com/youtube/v3/videos?' . http_build_query($params);
            Log::info('YT API:' . __CLASS__ . ' / ' . __FUNCTION__ . '()', ['url' => $url]);

            $res = file_get_contents($url);
            $json = json_decode($res, true) ?: [];

            foreach ($json['items'] ?? [] as $v) {
                $sn = $v['snippet'] ?? [];
                $st = $v['statistics'] ?? [];
                $cd = $v['contentDetails'] ?? [];

                $out[] = [
                    // ids/refs
                    'videoId'       => $v['id'] ?? null,
                    'channelId'     => $sn['channelId'] ?? null,
                    'channelTitle'  => $sn['channelTitle'] ?? null,
                    'published'           => $sn['publishedAt'] ?? null,



                    // metadados do vídeo
                    'videoTitle'        => $sn['title'] ?? '',
                    'videoDesc'         => $sn['description'] ?? null,
                    'videoTags'         => $sn['tags'] ?? null,                 // array|nul
                    'videoCategId'      => $sn['categoryId'] ?? null,
                    'lang'         => $sn['defaultAudioLanguage'] ?? ($sn['defaultLanguage'] ?? null),
                    'thumbnail'         => $sn['thumbnails']['default']['url'] ?? null,

                    // stats
                    'viewCount'    => isset($st['viewCount'])    ? (int) $st['viewCount']    : null,
                    'likeCount'    => isset($st['likeCount'])    ? (int) $st['likeCount']    : null,
                    'commentCount' => isset($st['commentCount']) ? (int) $st['commentCount'] : null,

                    // duração
                    //'videoDurationIso'  => $cd['duration'] ?? 'PT0S',
                    // se quiser em segundos: descomente e implemente ISO8601ToSeconds
                    'duration'      => $this->ISO8601ToSeconds($cd['duration'] ?? null),
                ];
            }
        }

        // ordena por data do vídeo (quando disponível)
        usort($out, fn($a, $b) => strcmp($a['videoDt'] ?? '', $b['videoDt'] ?? ''));
        return $out;
    }


    # 1 canal_id => 1 canal_details --------- 
    # 550 canais_ids => 550 canais_details --------- 



    public function getCanaisDetailsByListCanaisIds(array $channelIds): array
    {
        $apiKey = env('YOUTUBE_API_KEY');

        $channelIds = array_values(array_unique(array_filter($channelIds)));
        if (!$channelIds) return [];

        $out = [];

        foreach (array_chunk($channelIds, 50) as $pack) {
            $params = [
                'key'  => $apiKey,
                'id'   => implode(',', $pack),
                'part' => 'snippet,statistics,brandingSettings,contentDetails',
            ];

            $url  = 'https://www.googleapis.com/youtube/v3/channels?' . http_build_query($params);
            Log::info('YT API:' . __CLASS__ . ' / ' . __FUNCTION__ . '()', ['url' => $url]);

            $resp = file_get_contents($url);
            $json = json_decode($resp, true) ?: [];

            foreach ($json['items'] ?? [] as $ch) {
                $id = $ch['id'] ?? null;
                if (!$id) continue;

                $sn  = $ch['snippet'] ?? [];
                $st  = $ch['statistics'] ?? [];
                $br  = $ch['brandingSettings']['channel'] ?? [];

                $keywords = $this->parseBrandingKeywords($br['keywords'] ?? []);

                $out[$id] = [
                    'channelId'               => $id,
                    'channelTitle'            => $sn['title'] ?? null,
                    'channelDesc'             => $sn['description'] ?? null,
                    'channelDt'               => $sn['publishedAt'] ?? null,
                    'channelCountry'          => $sn['country'] ?? null,
                    'channelHandle'           => $sn['customUrl'] ?? null,
                    'channelThumb'            => $sn['thumbnails']['default']['url'] ?? null,
                    'channelDefaultLanguage'  => $sn['defaultLanguage'] ?? null,

                    'channelSubs'             => isset($st['subscriberCount']) ? (int) $st['subscriberCount'] : null,
                    'channelViews'            => isset($st['viewCount'])      ? (int) $st['viewCount']      : null,
                    'channelVideos'           => isset($st['videoCount'])     ? (int) $st['videoCount']     : null,

                    'channelKeywords'         => $keywords,

                    #'channelCategory'
                ];
            }
        }

        return $out; // keyBy channelId
    }


    function setPolarization(string $texto): ?float
    {
        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }

        // Limitar tamanho pra evitar custo absurdo / timeout
        if (mb_strlen($texto) > 3000) {
            $texto = mb_substr($texto, 0, 3000);
        }

        // Cache 30 dias por hash do texto
        $cacheKey = 'pol:google:' . sha1($texto);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($texto) {
            $apiKey = env('GOOGLE_API_KEY');
            if (!$apiKey) {
                // Sem chave, não tem como calcular
                return null;
            }

            $url = 'https://language.googleapis.com/v1/documents:analyzeSentiment?key=' . $apiKey;

            $payload = [
                'document' => [
                    'type'    => 'PLAIN_TEXT',
                    'content' => $texto,
                    // 'language' => 'pt', // se quiser forçar; senão ele detecta
                ],
                'encodingType' => 'UTF8',
            ];

            try {
                $response = Http::timeout(10)->post($url, $payload);

                if (!$response->successful()) {
                    // Opcional: logar erro
                    Log::warning('Google NLP sentiment error', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    return null;
                }

                $data = $response->json();

                // Estrutura típica:
                // $data['documentSentiment']['score']  => -1.0 a 1.0
                // $data['documentSentiment']['magnitude']
                $score = $data['documentSentiment']['score'] ?? null;
                $magnitude = $data['documentSentiment']['magnitude'] ?? null;

                if (!is_numeric($score)) {
                    return null;
                }

                Log::info('nlp2', [$texto, $score, $magnitude]);

                $polarization = $this->polarizacaoGoogle($score, $magnitude);

                return is_numeric($polarization) ? round($polarization, 2) : null;

            } catch (\Throwable $e) {
                \Log::error('Google NLP sentiment exception', [
                    'msg' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }



    function setTox($txt)
    {
        #return mt_rand() / mt_getrandmax();

        $apiKey = env("PERSPECTIVE_API");

        #dump($apiKey);

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

        #dd($response);

        if (curl_errno($ch)) {
            dd('Erro cURL: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $res = json_decode($response, true);

        if (!is_array($res)) {
            return null;
        }

        if (isset($res['attributeScores']['TOXICITY']['summaryScore']['value'])) {
            $tox = round($res['attributeScores']['TOXICITY']['summaryScore']['value'], 3);
        } else {
            $tox = null; // ou 0, ou -1, ou qualquer valor que faça sentido no seu contexto
        }
        return $tox;
    }


    function polarizacaoGoogle(float $score, float $magnitude): ?float
    {
        // 1) Descarta magnitudes muito baixas
        if ($magnitude < 0.1 || $score == 0) {
            return null;
        }

        // 2) Se for score zero mas magnitude alta (ambiguidade), descarta
        // if ($score == 0.0 && $magnitude > 0.3) {
        //     return null;
        // }

        // 3) Calcula polarização
        $polar = $score * $magnitude * 100;

        // 4) Corta nos limites [-100, 100]
        if ($polar > 100) {
            $polar = 100;
        } elseif ($polar < -100) {
            $polar = -100;
        }

        return $polar;
    }





    public function setPolarization3333333333333(string $texto): ?float
    {
        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }

        // Limitar tamanho pra não estourar token / custo
        if (mb_strlen($texto) > 3000) {
            $texto = mb_substr($texto, 0, 3000);
        }

        // cache 30 dias por hash do texto (igual tua lógica antiga)
        $cacheKey = 'polarization:' . sha1($texto);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($texto) {

            $schema = $this->sentimentSchema();

            // Prompt bem explicadinho pra PT/EN
            $prompt = <<<PROMPT
                Você é um analisador de sentimento especializado em texto curto ou médio.

                TAREFA:
                - Analise o sentimento global do texto a seguir.
                - Considere o tom geral, emoção predominante, polaridade (positivo/negativo) e intensidade.
                - O texto pode estar em português ou em inglês.

                ESCALA:
                - Score deve ser um número entre -100 e 100.
                    -100  = extremamente negativo / hostil
                    -50   = claramente negativo
                    0   = neutro ou misto equilibrado
                    +50   = claramente positivo
                    +100  = extremamente positivo / entusiasmado

                MAPEAMENTO DE RÓTULOS:
                - very_negative: score <= -60
                - negative:      -60 < score < -10
                - neutral:       -10 <= score <= 10
                - positive:      10 < score < 60
                - very_positive: score >= 60

                IMPORTANTE:
                - Se o texto estiver em português, explique em português.
                - Se o texto estiver em inglês, explique em inglês.
                - A saída DEVE seguir estritamente o schema fornecido (score, label, explanation).

                TEXTO ALVO:
                \"\"\"{$texto}\"\"\"
                PROMPT;

            try {
                $response = Prism::structured()
                    ->using(Provider::OpenAI, 'gpt-4o') // ou o modelo que você estiver usando
                    ->withSchema($schema)
                    ->withProviderOptions([
                        'schema' => [
                            'strict' => true, // força aderência ao schema quando suportado
                        ],
                    ])
                    ->withPrompt($prompt)
                    ->asStructured();

                $data = $response->structured ?? [];

                $score = $data['score'] ?? null;

                // Se vier string, tenta converter
                if (!is_null($score) && !is_float($score) && !is_int($score)) {
                    if (is_string($score)) {
                        $score = floatval($score);
                    }
                }

                if (!is_numeric($score)) {
                    Log::warning('Polarization: score não numérico', ['data' => $data]);
                    return null;
                }

                $score = (float) $score;

                // Garantir limite -100..100
                if ($score > 100)  $score = 100;
                if ($score < -100) $score = -100;

                // Se você quiser equivalente -1..1 em algum lugar:
                // $normalized = $score / 100.0;

                return round($score, 2);
            } catch (\Throwable $e) {
                Log::error('Erro ao chamar Prism/OpenAI em setPolarization', [
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    protected function sentimentSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'sentiment_analysis',
            description: 'Sentiment score from -100 (very negative) to 100 (very positive)',
            properties: [
                new NumberSchema(
                    name: 'score',
                    description: 'Sentiment score between -100 (very negative) and 100 (very positive), 0 = neutral'
                ),
                new StringSchema(
                    name: 'label',
                    description: 'One of: very_negative, negative, neutral, positive, very_positive'
                ),
                new StringSchema(
                    name: 'explanation',
                    description: 'Short explanation (in Portuguese if text is PT-BR, otherwise in English)'
                ),
            ],
            requiredFields: ['score', 'label', 'explanation']
        );
    }



    function setPolarization22222222222(string $texto): ?float
    {
        #return mt_rand(-100, 100);

        $texto = trim($texto);
        if ($texto === '') return null;

        // opcional: limitar tamanho pra evitar timeout/quotas
        if (mb_strlen($texto) > 3000) {
            $texto = mb_substr($texto, 0, 3000);
        }

        dump($texto);
        // cache 30 dias por hash do texto
        #$cacheKey = 'nlp:' . sha1($texto);
        #return Cache::remember($cacheKey, now()->addDays(30), function () use ($texto) {
        $url   = 'https://api.gotit.ai/NLU/v1.5/Analyze';
        $basic = env('GOTIT_API_KEY') . ':' . env('GOTIT_SECRET_KEY');

        $payload = json_encode([
            "T" => $texto,
            #"T" => "Victor comeu uma pizza deliciosa.",
            "S" => true,
            #"EM" => true
        ]);
        $headers = [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($basic),
        ];

        ###################################







        // $data_array = [];
        // $data_array["T"] = "Victor comeu uma pizza deliciosa.";
        // $data_array["S"] = true;
        // $data = json_encode($data_array);
        // $options = array(
        //     'http' => array(
        //         'header' => $headers,
        //         'method' => 'POST',
        //         'content' => $data
        //     )
        // );
        // $context  = stream_context_create($options);
        // $result = file_get_contents('https://api.gotit.ai/NLU/v1.5/Analyze', false, $context);
        // $result = json_decode($result, true);
        // dd($result);










        ###################################
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
            dd('NLP timeout/erro : ' . curl_error($ch));
            $this->msg('NLP timeout/erro : ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http !== 200) {
            dd('NLP HTTP não-200: ' . $http);
            $this->msg('NLP HTTP não-200: ' . $http);
            return null;
        }

        dd($result);

        $res = json_decode($result, true);
        $score = data_get($res, 'sentiment.score'); // pode vir 0 (falsy), então não use empty()

        dd($score);

        return is_numeric($score) ? round((float)$score, 2) : null;
        #});
    }





    ########################################## UPSERTS 4

    public function upsertBusca(
        string $q
        #, Tarefa $tarefa
    ): Busca {

        $tarefa = $this->getTarefa();
        $slug = Str::slug($q);
        // Usamos firstOrNew para conseguir setar tarefa_id com segurança
        $busca = Busca::firstOrNew(['slug' => $slug, 'tarefa_id' => $tarefa->id]);
        $busca->q = $q;
        $busca->tarefa()->associate($tarefa);
        $busca->save();

        return $busca;
    }

    ############## atencao existe uma relacao implicita de aninhamento
    # o canal e superior em relacao ao video
    # entao pode-se add um canal sem video assoc, mas nao o contrario

    // --- CANAL ---
    public function upsertCanal(array $ch, ?Busca $busca = null): Canal
    {
        #$tarefa = $this->resolveTarefa($tarefa);
        $tarefa = $this->getTarefa();
        $canal = Canal::firstOrNew(['youtube_id' => $ch['youtube_id'], 'tarefa_id' => $tarefa->id]);

        $canal->fill([
            'nome'       => $ch['nome'] ?? null,
            'slug'       => isset($ch['nome']) ? Str::slug($ch['nome']) : $canal->slug,
            #'youtube_id' => $ch['youtube_id'] ?? null,
            #'desc'       => $ch['desc'] ?? null,

            'desc'       => $ch['desc'] ? $this->squashSpaces($ch['desc']) : null,


            #'links'      => $ch['links'] ?? null,
            'keywords'   => $ch['keywords'] ?? [],
            'inscritos'  => $ch['inscritos'] ?? null,
            'views'      => $ch['views'] ?? null,
            'videos'     => $ch['videos'] ?? null,
            'dt'         => $this->toDate($ch['dt'] ?? null),
            'local'      => $ch['local'] ?? null,
            'categ'      => $ch['categ'] ?? null,
            'score'      => $ch['score'] ?? null,
            'min'        => $ch['min'] ?? null,
            'max'        => $ch['max'] ?? null,
            'engagement' => $ch['engagement'] ?? null,
            'frequency'  => $ch['frequency'] ?? null,
            'length'     => $ch['length'] ?? null,
        ]);

        if ($busca)
            $canal->busca()->associate($busca);

        $canal->tarefa()->associate($tarefa);
        $canal->save();

        #dd($canal);
        return $canal;
    }

    // --- VIDEO ---
    public function upsertVideo(array $vd, Canal $canal, ?Busca $busca = null): Video
    {

        $tarefa = $this->getTarefa();
        $video = Video::firstOrNew(['cod' => $vd['cod'], 'canal_id' => $canal->id, 'tarefa_id' => $tarefa->id]);

        $video->fill([
            'nome'       => $vd['nome'] ?? null,
            'slug'       => isset($vd['nome']) ? Str::slug($vd['nome']) : $video->slug,

            'desc'       => $vd['desc'] ? $this->squashSpaces($vd['desc']) : null,

            'caption'    => $vd['caption'] ?? null,
            'hashtags'   => $vd['hashtags'] ?? [],
            'comments'   => $vd['comments'] ?? null,
            'likes'      => $vd['likes'] ?? null,
            'dislikes'   => $vd['dislikes'] ?? null,
            'views'      => $vd['views'] ?? null,
            'favorites'  => $vd['favorites'] ?? null,
            'duration' => $this->ISO8601ToSeconds($vd['duration'] ?? null),

            'categ_id'   => $vd['categ_id'] ?? null,
            'lang'       => $vd['lang'] ?? null,
            'dt'         => $this->toDateTime($vd['dt'] ?? null),
        ]);

        $video->canal()->associate($canal);
        $video->tarefa()->associate($tarefa);

        if ($busca)
            $video->busca()->associate($busca);

        $video->save();

        return $video;
    }


    public function upsertComentario(array $c, Video $video): Comentario
    {


        #dd($c);

        $cod = $c['cod'];
        $tarefa = $this->getTarefa();
        $coment = Comentario::firstOrNew([
            'cod'       => $cod,
            'video_id'  => $video->id,
            'tarefa_id' => $tarefa->id,
        ]);

        // 3) Preenche campos mutáveis
        $coment->fill([
            'username'  => $c['username'] ?? null,
            'texto'     => $c['texto'] ? $this->squashSpaces($c['texto']) : '',
            'likes'     => $c['likes'] ?? null,
            'dislikes'  => $c['dislikes'] ?? null,
            'dt'        => $this->toDateTime($c['dt'] ?? null),
            'tox'       => $c['tox'] ?? null,
        ]);

        $coment->video()->associate($video);
        $coment->tarefa()->associate($tarefa);
        $coment->save();
        return $coment;
    }
}
