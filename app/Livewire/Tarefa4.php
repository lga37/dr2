<?php

namespace App\Livewire;

use Log;
use App\Traits\Comum;
use App\Models\Tarefa;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;


class Tarefa4 extends Component
{

    use Comum;

    # params comuns ###########################################
    public array $canais = [];
    public $buscas = [];
    public array $videos_dos_canais = [];
    public array $selecionados = [];
    public array $deselected = [];
    public string $addInput = '';

    public array $checked = [];       // << id => true (checado = incluir)

    public string $feedback = '';       // textarea
    public bool $mostrarAvaliacao = true;
    public bool $mostrarFeedback = false;
    protected array $sessionPrefixes = ['t4_query', 't4_canais', 't4_checked', 't4_buscas', 't4_unchecked', 't4_videos_dos_canais']; // ajuste como preferir

    public array $unchecked = []; // ids que o usuário desmarcou

    function getTipoTarefa(): string
    {
        return 't4';
    }

    public function mount()
    {
        $this->selecionados = Session::get('t4_canais', []);
        $this->buscas       = Session::get('t4_buscas', []);
        $this->checked      = Session::get('t4_checked', []);     // <<< Faltava
        $this->unchecked    = Session::get('t4_unchecked', []);   // <<< Para lembrar quem foi desmarcado
        $this->query        = Session::get('t4_query', '');

        // Primeira carga da página, se ainda não há decisão alguma:
        if (empty($this->checked) && !empty($this->buscas)) {
            $this->checked = $this->idsFromBuscas($this->buscas);
        }

        Session::put('t4_canais',   $this->selecionados);
        Session::put('t4_buscas',   $this->buscas);
        Session::put('t4_checked',  $this->checked);
        Session::put('t4_unchecked', $this->unchecked);

        if(!empty(Session::get('t4_canais'))){
            $this->mostrarFeedback = true;

        }

    }

    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback'          => $this->feedback,

        ];
        $status             = 1;
        $finished_at        = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));

        $msg = $t ? 'Obrigado! Sua tarefa #' . $tarefa_id . ' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #' . $tarefa_id;

        $this->clearSelecionados();
        $this->msg($msg, 'info');

        #dd($t);
    }


    public function validarTarefa4()
    {
        // carrega seleção do array central
        $this->selecionados    = Session::get('t4_canais', $this->selecionados);

        Session::forget('t4_videos');
        $sessVideos = [];

        #dd($this->selecionados);

        foreach ($this->selecionados as $canalId => $raw) {

            $q       = $raw['busca'] ?? '[erro]';
            $buscaBD = $this->upsertBusca($q);

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

            $videos = $this->getAllVideos(
                $raw['channelId'],
                $raw['channelDt'],
                100,
                5,
                1,
                $raw['channelVideos']
            );

            if (empty($videos) || !is_array($videos)) {
                dd('erro');
            }

            $polarizations = [];
            foreach ($videos as $v) {
                $titulo = $v['videoTitle'] ?? '';
                $desc   = $v['videoDesc']  ?? '';
                $texto  = trim($titulo . "\n" . $desc);

                if ($texto === '' || strlen($texto) <= 10) {
                    continue;
                }

                #if ($tox100 !== null) $scores[] = $tox100;

                $tox = $this->setTox($texto);
                #$polarization = $this->setPolarization($texto);
                if(is_null($tox)){
                    continue;
                }
                $polarization = 100 * $tox;
                

                #$rawTox = $this->setTox($texto);     // 0..1 (provável)
                $rawTox = $tox;                 // reaproveita

                $tox100 = is_numeric($rawTox) ? round($rawTox * 100, 2) : null;

                // Log::info('T4 tox debug', [
                //   'canal' => $canalId,
                //   'video' => $v['videoId'] ?? null,
                //   'len'   => strlen($texto),
                //   'sha1'  => sha1($texto),
                //   'raw'   => $rawTox,
                //   'tox100'=> $tox100,
                //   'title' => mb_substr($titulo, 0, 60),
                // ]);

                if ($polarization === null) {
                    continue;
                }

                $polarizations[] = $polarization;
            }

            if (count($polarizations) === 0) {
                dd('erro2');
            }

            Log::info('media', [ $canalId, $polarizations ]);

            // Média simples (-1 a +1) -> percent
            $media = array_sum($polarizations) / max(count($polarizations), 1);  // fica -100..100
            $media = round($media, 2); // 0 a 100

            // ⚠️ grava direto no ARRAY CENTRAL
            $this->selecionados[$canalId]['polariz'] = $media;

            Log::info('media', [ $canalId, $media ]);


            // salva vídeos no BD
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

                $videoBD = $this->upsertVideo($vd, $canalBD, $buscaBD);
            }

            // ordena vídeos por data
            $ordenados = collect($videos)
                ->filter(fn($c) => !empty($c['videoId']))
                ->sortBy(fn($c) => $c['videoDt'])
                ->values()
                ->toArray();

            $this->videos_dos_canais[$canalId] = $ordenados;
            $sessVideos[$canalId]              = $ordenados;
        }

        Session::put('t4_videos', $sessVideos);

        // ATUALIZA o array central na sessão (já com monet + polar)
        Session::put('t4_canais', $this->selecionados);

        Session::put('t4_result', [
            'selecionados'  => $this->selecionados,
            'videos'        => $this->videos_dos_canais,
            'buscas'        => $this->buscas,
            // se quiser ainda ter uma chave "grafico", manda o próprio selecionados
            'grafico'       => $this->selecionados,
        ]);

        return redirect()->route('tarefa4'); // ou o nome que você tiver

    }





    private function seedCheckedFromBuscas(): void
    {
        if (empty($this->buscas)) 
            return;

        foreach ($this->buscas as $r) {
            $id = $r['channelId'] ?? null;
            if (!$id) 
                continue;

            // NUNCA re-adiciona quem o usuário desmarcou
            if (isset($this->unchecked[$id])) 
                continue;

            // só marca por padrão se ainda não existe decisão do usuário
            if (!array_key_exists($id, $this->checked)) {
                $this->checked[$id] = true;
            }
        }

        Session::put('t4_checked', $this->checked);
    }






    private function idsFromBuscas(array $buscas): array
    {
        $ids = [];
        foreach ($buscas as $r) {
            if (!empty($r['channelId'])) {
                $ids[$r['channelId']] = true;
            }
        }
        return $ids;
    }

    public function updatedChecked($value, $key): void
    {
        // $key é "UCxxxx..." (channelId)
        if (empty($value)) {
            // 3️⃣ Desmarcou -> some de checked
            unset($this->checked[$key]);
        } else {
            $this->checked[$key] = true;
        }
        Session::put('t4_checked', $this->checked);
    }

    public function toggleCheck(string $id, bool $on): void
    {
        if ($on)
            $this->checked[$id] = true;
        else
            unset($this->checked[$id]);
        Session::put('t4_checked', $this->checked);
    }

    public function updated(string $name, $value): void
    {
        if (Str::startsWith($name, 'checked.')) {
            $id = Str::after($name, 'checked.');
            if ($value) {
                // marcou
                $this->checked[$id] = true;
                unset($this->unchecked[$id]);
            } else {
                // desmarcou
                unset($this->checked[$id]);
                $this->unchecked[$id] = true;
            }
            Session::put('t4_checked',   $this->checked);
            Session::put('t4_unchecked', $this->unchecked);
        }
    }





    public function addTodos(): void
    {
        // 1) Pega todos os channelId que estão na grid
        $idsGrid     = collect($this->buscas)->pluck('channelId')->filter()->values()->all();

        // 2) Interseção entre IDs da grid e checkboxes marcados
        $idsChecados = array_values(array_intersect($idsGrid, array_keys($this->checked)));

        // 3) Quem está na grid mas NÃO está checado
        $idsNaoChec  = array_values(array_diff($idsGrid, $idsChecados));

        // 4) Mapa channelId => linha completa da busca
        $map = [];
        foreach ($this->buscas as $r) {
            if (!empty($r['channelId'])) {
                // garante que sempre exista o campo "busca"
                $r['busca'] = $r['busca'] ?? ($this->query ?? null);
                $map[$r['channelId']] = $r;
            }
        }

        // Garante que selecionados é um array
        if (!is_array($this->selecionados)) {
            $this->selecionados = [];
        }

        // 5) Inclui/atualiza os checados no array de selecionados
        foreach ($idsChecados as $id) {
            $this->selecionados[$id] = $map[$id] ?? [];
        }

        // 6) Remove quem foi desmarcado
        foreach ($idsNaoChec as $id) {
            unset($this->selecionados[$id]);
        }

        // 7) Agora filtra por monetização VidIQ:
        //    - chama a função do VidIQ para cada canal selecionado
        //    - se não tiver valor, REMOVE o canal
        //    - se tiver, cria o campo 'monete' com o valor
        $removidosSemMonet = 0;

        foreach ($idsChecados as $channelId) {

            // pode ter sido removido na etapa anterior
            if (!isset($this->selecionados[$channelId])) {
                continue;
            }

            // Aqui você consulta o VidIQ
            $monet = $this->getVidIqMonthlyAvgUsd($channelId);

            // Se não veio nada / null / zero, remove o canal
            if (empty($monet)) {
                unset($this->selecionados[$channelId]);
                $removidosSemMonet++;
                continue;
            }

            // Se tiver monetização, guarda no registro
            // (troca 'monete' pelo nome que preferir)
            $this->selecionados[$channelId]['monetiz'] = $monet;
        }

        // 8) Atualiza sessão só DEPOIS de filtrar por monetização
        Session::put('t4_canais', $this->selecionados);

        // 9) Esconde avaliação
        $this->mostrarAvaliacao = false;

        // 10) Feedback pro usuário
        $this->msg(
            "OK: " . count($this->selecionados) . " incluído(s), " .
                count($idsNaoChec) . " excluído(s) manualmente, " .
                $removidosSemMonet . " sem monetização (filtrado(s) pelo VidIQ).",
            'info'
        );
    }



    public function repopularMonetizSelecionados()
    {
        // garante que é array
        if (!is_array($this->selecionados) || empty($this->selecionados)) {
            return;
        }

        foreach ($this->selecionados as $channelId => $raw) {

            if (empty($raw['channelId'])) {
                continue;
            }

            // Consulta VidIQ (ou o que for)
            $monet = $this->getVidIqMonthlyAvgUsd($channelId); // pode ser null

            // guarda no array central
            $this->selecionados[$channelId]['monetiz'] = $monet;
        }

        // atualiza sessão com o ARRAY CENTRAL
        Session::put('t4_canais', $this->selecionados);
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







    ######## essa vai ser a funcao q finaliza tudo
    public function avaliarCanais333333333333333333333333(): void
    {

        if (count($this->selecionados) < 10)
            return;

        $this->mostrarAvaliacao = true;
        $this->videos_dos_canais  = [];
    }


    #[Layout("layouts/app")]
    public function render()
    {

        $this->seedCheckedFromBuscas();  // garante checados na 1ª exibição e não re-checa quem foi desmarcado

        #$this->seedCheckedFromBuscas();   // ✅ garante marcados no 1º paint

        return view('livewire.tarefa4');
    }
}


