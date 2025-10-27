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
use App\Services\YoutubeStorage;
use Illuminate\Support\Facades\Auth;
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
        $tipo_tarefa = $this->getTipoTarefa(); #t1 t2

        $this->query = $this->normalizeQuery($value);

        Session::put($tipo_tarefa . '_query', $this->query);

        // Se quiser buscar a cada mudança, faça com um gate simples:
        if (mb_strlen($this->query) >= 2) {
            // cuidado para não spammar a API:
            if ($tipo_tarefa == 't1') {
                $this->getVideos();
            } else {
                $this->getCanais();
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

        #$this->getVideos();


        $forceRefresh = false;
        $this->buscas = $this->getVideos($forceRefresh);
        Session::put($tipo . '_query', $this->query);


        #Session::put('t1_query', $this->query);
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





    #comum
    public function removeSelecionado(string $registroId): void
    {
        unset($this->selecionados[$registroId]);
        $this->persistSelecionados();
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

    /** Mesmo tokenizer que você já usa */
    private function parseBrandingKeywords_depois333333333333333333($raw): array
    {
        $raw = is_array($raw) ? implode(' ', $raw) : (string) $raw;
        $raw = str_replace(['“', '”', '„', '«', '»'], '"', $raw);
        $raw = str_replace([',', ';', '|'], ' ', $raw);
        preg_match_all('/"([^"]+)"|(\S+)/u', $raw, $m);
        $kw = [];
        foreach ($m[0] as $i => $full) {
            $t = $m[1][$i] !== '' ? $m[1][$i] : $m[2][$i];
            $t = trim($t, " \t\n\r\0\x0B\"'");
            if ($t !== '') $kw[] = $t;
        }
        return $kw;
    }









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
            'texto'     => $c['texto'] ? $this->squashSpaces($c['texto']) : null,
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
