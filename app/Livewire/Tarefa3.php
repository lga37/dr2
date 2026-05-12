<?php

namespace App\Livewire;

use App\Models\Tarefa;
use App\Traits\Comum;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Tarefa3 extends Component
{
    use Comum;

    public function getTipoTarefa(): string
    {
        return 't3';
    }

    public array $canais = [];

    public $buscas = [];

    public array $videos_dos_canais = [];

    public array $selecionados = [];

    public string $addInput = '';

    public string $feedback = '';       // textarea

    public bool $mostrarAvaliacao = false;

    protected array $sessionPrefixes = ['t3_query', 't3_canais']; // ajuste como preferir

    public array $word_ranking = []; // [canalId => [['word'=>..., 'count'=>...], ...]]

    public array $cloudTokens = [];

    public function mount() {}

    public array $pmResult = [];

    public function validarTarefa3()
    {
        $this->selecionados = Session::get('t3_canais', $this->selecionados);
        $this->mostrarAvaliacao = true;

        Session::forget('t3_videos');
        $sessVideos = [];

        $this->avaliarPolarizacaoMonetizacao();

        foreach ($this->selecionados as $canalId => $raw) {

            $q = $raw['busca'] ?? '[erro]';
            $buscaBD = $this->upsertBusca($q);
            // dump($buscaBD);

            $ch = [
                'youtube_id' => $raw['channelId'],
                'nome' => $raw['channelTitle'] ?? null,
                'keywords' => $raw['channelKeywords'] ?? [],
                'handle' => $raw['channelHandle'] ?? null,
                'inscritos' => $raw['channelSubs'] ?? null,
                'views' => $raw['channelViews'] ?? null,
                'videos' => $raw['channelVideos'] ?? null,
                'dt' => $raw['channelDt'] ?? null,
                'local' => $raw['channelCountry'] ?? null,
                'categ' => $raw['channelCategory'] ?? null,
                'desc' => $raw['channelDesc'] ?? null,
            ];

            $canalBD = $this->upsertCanal($ch, $buscaBD);
            // dump($canalBD);

            $videos = $this->getAllVideos($raw['channelId'], $raw['channelDt'], 100, 10, 1, $raw['channelVideos']);

            foreach ($videos as $vd) {
                $vd = [
                    'cod' => $vd['videoId'],
                    'nome' => $vd['videoTitle'] ?? null,
                    'desc' => $vd['videoDesc'] ?? null,
                    'hashtags' => $vd['videoTags'] ?? [],
                    'comments' => $vd['videoCommentCount'] ?? null,
                    'likes' => $vd['videoLikeCount'] ?? null,
                    'views' => $vd['videoViewCount'] ?? null,
                    'duration' => $vd['videoDuration'] ?? null,
                    'lang' => $vd['videoLang'] ?? null,
                    'dt' => $vd['videoDt'] ?? null,
                    'categ_id' => $vd['videoCategId'] ?? null,
                ];

                // dump($vd);
                $videoBD = $this->upsertVideo($vd, $canalBD, $buscaBD);
            }

            $ordenados = collect($videos)
                ->filter(fn ($c) => ! empty($c['videoId']))
                ->sortBy(fn ($c) => $c['videoDt'])
                ->values()
                ->toArray();

            $this->videos_dos_canais[$canalId] = $ordenados;
            $sessVideos[$canalId] = $ordenados;
        }

        Session::put('t3_videos', $sessVideos);

        Session::put('t3_result', [
            'selecionados' => $this->selecionados,
            'videos' => $this->videos_dos_canais,
            'buscas' => $this->buscas,

        ]);
    }

    public function avaliarPolarizacaoMonetizacao(): void
    {
        $selecionados = Session::get('t3_canais', []);

        if (count($selecionados) < 2) {
            return;
        }

        $resultado = [];
        $cores = ['green', 'red'];

        foreach (array_values($selecionados) as $idx => $canalRaw) {
            $channelId = $canalRaw['channelId'] ?? $canalRaw['youtube_id'] ?? null;

            if (! $channelId) {
                continue;
            }

            $channel = [
                'channelId' => $channelId,
                'channelTitle' => $canalRaw['channelTitle'] ?? $canalRaw['nome'] ?? null,
                'channelDesc' => $canalRaw['channelDesc'] ?? $canalRaw['desc'] ?? null,
                'channelDt' => $canalRaw['channelDt'] ?? $canalRaw['dt'] ?? null,
                'subscriberCount' => $canalRaw['channelSubs'] ?? $canalRaw['inscritos'] ?? null,
            ];

            $buckets = $this->pmt_bucket_periods($channel['channelDt'], 5);

            $videos = $this->getAllVideos($channelId, max: 50);

            $ids = collect($videos)->pluck('videoId')->filter()->values()->all();

            $videosOrdenados = $this->getVideoDetailsByListVideoIds($ids);

            $videosOrdenados = collect($videosOrdenados)
                ->filter(fn ($v) => ! empty($v['published']))
                ->sortBy('published')
                ->values()
                ->toArray();

            $bucketResults = [];

            foreach ($buckets as $bucket) {
                $videosBucket = collect($videosOrdenados)->filter(function ($v) use ($bucket) {
                    $dt = $v['published'] ?? null;

                    if (! $dt) {
                        return false;
                    }

                    return $dt >= $bucket['after'] && $dt <= $bucket['before'];
                })
                    ->values()
                    ->toArray();

                $bucketResults[] = [
                    'idx' => $bucket['idx'],
                    'label' => $bucket['label'],
                    'after' => $bucket['after'],
                    'before' => $bucket['before'],
                    'videos_count' => count($videosBucket),
                    'analysis' => $this->pmt_analisar_bucket_pm($channel, $videosBucket),
                ];
            }

            // $urlsCanal = [];
            // foreach ($bucketResults as $bucketResult) {
            //     $urlsCanal = array_merge(
            //         $urlsCanal,
            //         $bucketResult['analysis']['monetizacao_off_platform']['urls'] ?? []
            //     );
            // }
            // $urlsCanal = array_values(array_unique($urlsCanal));

            $monet = $this->pmt_monetizacao_video([], [
                'youtube_id' => $channelId,
                'nome' => $channel['channelTitle'],
                'desc' => $channel['channelDesc'],
            ]);

            // $resultado[$channelId] = [
            //     'cor' => $cores[$idx] ?? 'slate',
            //     'channel' => $channel,
            //     'total_videos_coletados' => count($videosOrdenados),
            //     'buckets' => $bucketResults,
            //     'monetizacao_canal' => $monet,
            //     // novas chaves para o Blade
            //     'urls_total' => count($urlsCanal),
            //     'urls' => $urlsCanal,
            // ];

            ####################################################################
            $urlsCanal = [];

            foreach ($bucketResults as $bucketResult) {
                $urlsCanal = array_merge(
                    $urlsCanal,
                    $bucketResult['analysis']['monetizacao_off_platform']['urls'] ?? []
                );
            }

            $urlsCanal = array_values(array_unique($urlsCanal));

            $resultado[$channelId] = [
                'cor' => $cores[$idx] ?? 'slate',
                'channel' => $channel,
                'total_videos_coletados' => count($videosOrdenados),
                'buckets' => $bucketResults,
                'monetizacao_canal' => $monet,
                'urls_total' => count($urlsCanal),
                'urls' => $urlsCanal,
            ];

        }

        $this->pmResult = $resultado;
        // Session::put('pm_result', $resultado);
    }

    private function buildWordCloudTokens(array $items, int $maxWords = 60): array
    {
        // items: [['word'=>'php','count'=>11], ...]
        $items = collect($items)
            ->filter(fn ($x) => ! empty($x['word']) && ! empty($x['count']))
            ->sortByDesc('count')
            ->take($maxWords)
            ->values()
            ->toArray();

        if (! $items) {
            return [];
        }

        $counts = array_map(fn ($x) => (int) $x['count'], $items);
        $min = min($counts);
        $max = max($counts);

        // paleta pastel (texto apenas)
        $palette = [
            '#2563eb', '#7c3aed', '#db2777', '#059669', '#d97706',
            '#0891b2', '#dc2626', '#4f46e5', '#0f766e', '#9333ea',
        ];

        $tokens = [];
        foreach ($items as $it) {
            $c = (int) $it['count'];

            // escala 12..44px (ajuste se quiser)
            if ($max === $min) {
                $size = 22;
            } else {
                $t = ($c - $min) / ($max - $min);   // 0..1
                $size = (int) round(12 + $t * 32);  // 12..44
            }

            $tokens[] = [
                'word' => (string) $it['word'],
                'count' => $c,
                'size' => $size,
                'color' => $palette[array_rand($palette)],
                // leve variação de opacidade via inline style
                'alpha' => [0.55, 0.65, 0.75, 0.85, 0.95][array_rand([0, 1, 2, 3, 4])],
            ];
        }

        // aleatoriza a ordem (nuvem ≠ ranking)
        shuffle($tokens);

        return $tokens;
    }

    protected function stopwordsPT(): array
    {
        return array_unique(array_merge(
            $this->stopwordsArtigos(),
            $this->stopwordsPreposicoes(),
            $this->stopwordsConjuncoes(),
            $this->stopwordsPronomesPessoais(),
            $this->stopwordsPronomesPossessivos(),
            $this->stopwordsPronomesDemonstrativos(),
            $this->stopwordsAdverbios(),
            $this->stopwordsQuantificadores(),
            $this->stopwordsVerbosAuxiliares(),
            $this->stopwordsYoutube(),
        ));
    }

    public function normalizeTextForWords(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // remove urls
        $text = preg_replace('~https?://\S+~u', ' ', $text);

        // remove @handles e #hashtags (ou mantenha o termo sem o símbolo)
        $text = preg_replace('/[@#]/u', '', $text);

        // remove números
        $text = preg_replace('/\d+/u', ' ', $text);

        // remove pontuação/símbolos (mantém letras e espaço)
        $text = preg_replace('/[^\p{L}\s]+/u', ' ', $text);

        // remove acentos (melhor esforço)
        if (class_exists(\Transliterator::class)) {
            $text = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC;', $text);
        } else {
            $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        }

        // normaliza espaços
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    public function rankWordsFromVideoTitles(array $videos, int $top = 60, int $minLen = 3): array
    {
        $titles = collect($videos)
            ->pluck('videoTitle')
            ->filter()
            ->implode(' ');

        $clean = $this->normalizeTextForWords($titles);
        if ($clean === '') {
            return [];
        }

        $stop = array_fill_keys($this->stopwordsPT(), true);

        $counts = [];
        foreach (explode(' ', $clean) as $w) {
            if (mb_strlen($w, 'UTF-8') < $minLen) {
                continue;
            }
            if (isset($stop[$w])) {
                continue;
            }

            $counts[$w] = ($counts[$w] ?? 0) + 1;
        }

        arsort($counts);

        return collect($counts)
            ->take($top)
            ->map(fn ($count, $word) => [
                'word' => $word,
                'count' => $count,
            ])
            ->values()
            ->toArray();
    }

    protected function stopwordsArtigos(): array
    {
        return [
            'a', 'o', 'as', 'os',
            'um', 'uma', 'uns', 'umas',
            // ############################# outras q fui pondo conforme
            'que', 'se', 'voce',
        ];
    }

    protected function stopwordsPreposicoes(): array
    {
        return [
            'de', 'do', 'da', 'dos', 'das',
            'em', 'no', 'na', 'nos', 'nas',
            'por', 'para', 'pra', 'pro',
            'com', 'sem', 'sobre', 'entre',
            'até', 'após', 'antes', 'contra',
        ];
    }

    protected function stopwordsConjuncoes(): array
    {
        return [
            'e', 'ou', 'mas', 'porque', 'pois',
            'como', 'se', 'quando', 'enquanto',
            'logo', 'portanto', 'todavia', 'contudo',
        ];
    }

    protected function stopwordsPronomesPessoais(): array
    {
        return [
            'eu', 'tu', 'ele', 'ela', 'nós', 'vos', 'eles', 'elas',
            'me', 'te', 'se', 'lhe', 'lhes', 'nos', 'vos',
        ];
    }

    protected function stopwordsPronomesPossessivos(): array
    {
        return [
            'meu', 'minha', 'meus', 'minhas',
            'seu', 'sua', 'seus', 'suas',
            'nosso', 'nossa', 'nossos', 'nossas',
        ];
    }

    protected function stopwordsPronomesDemonstrativos(): array
    {
        return [
            'este', 'esta', 'estes', 'estas',
            'esse', 'essa', 'esses', 'essas',
            'aquele', 'aquela', 'aqueles', 'aquelas',
            'isso', 'isto', 'aquilo',
        ];
    }

    protected function stopwordsAdverbios(): array
    {
        return [
            'muito', 'muita', 'muitos', 'muitas',
            'pouco', 'pouca', 'poucos', 'poucas',
            'mais', 'menos', 'também', 'ainda',
            'sempre', 'nunca', 'hoje', 'ontem', 'amanhã',
            'já', 'não', 'sim',
        ];
    }

    protected function stopwordsQuantificadores(): array
    {
        return [
            'todo', 'toda', 'todos', 'todas',
            'cada', 'algum', 'alguma', 'alguns', 'algumas',
            'nenhum', 'nenhuma', 'muitos', 'muitas',
        ];
    }

    protected function stopwordsVerbosAuxiliares(): array
    {
        return [
            'ser', 'estar', 'ter', 'haver', 'ir', 'vir',
            'foi', 'era', 'são', 'é', 'está', 'estão',
            'tem', 'têm', 'teve', 'tinham', 'vai', 'vão',
        ];
    }

    protected function stopwordsYoutube(): array
    {
        return [
            'live', 'ao', 'vivo', 'oficial', 'completo', 'completa',
            'parte', 'episodio', 'episódio', 'ep',
            'podcast', 'entrevista', 'debate', 'evento',
        ];
    }

    public function salvarFeedback(): void
    {

        $tarefa_id = $this->getTarefaId();
        $dados = [
            'feedback' => $this->feedback,

        ];
        $status = 1;
        $finished_at = now();

        $t = Tarefa::find($tarefa_id)->update(compact('dados', 'status', 'finished_at'));

        $msg = $t ? 'Obrigado! Sua tarefa #'.$tarefa_id.' foi concluída COM SUCESSO.' : 'Erro ao completar tarefa #'.$tarefa_id;

        $this->clearSelecionados();
        $this->msg($msg, 'info');

        // dd($t);
    }

    // aqui e o caso de ter restricao - atencao aquiiiiiiii
    public function add2(string $canalId): void
    {
        dd('gg');
        if (! isset($this->selecionados[$canalId])) {
            $tem = false;
            // dd($this->buscas);
            foreach ($this->buscas as $reg_canal) {
                if ($reg_canal['channelId'] == $canalId) {
                    $data = $reg_canal;
                    $tem = true;
                    break;
                }
            }
            if (! $tem) {
                $dadosCanal = $this->getCanaisDetailsByListCanaisIds([$canalId]);
                $data = $dadosCanal[$canalId] ?? null;   // pega só o registro do canal
                if (! $data) {
                    $this->msg('Não foi possível obter os detalhes do canal.', 'error');

                    return;
                }
                $data['q'] = '--';
            }
            if (is_array($data) && ! empty($data)) {

                if ($reg_canal['channelSubs'] >= 5000 && $reg_canal['channelVideos'] <= 1000) {
                    $this->selecionados[$canalId] = $data;

                    $this->persistSelecionados();
                    $this->msg('Registro '.$canalId.' adicionado corretamente.', 'success');
                } else {
                    $this->msg('Registro '.$canalId.' deve ter +5K incritos e -1K videos para essa tarefa.', 'warn');
                }
            }
        } else {
            $this->msg('Nao adicionado pois já consta', 'error');
        }
    }

    #[Layout('layouts/app')]
    public function render()
    {
        return view('livewire.tarefa3');
    }
}
