<?php

namespace App\Livewire;

use App\Models\Tarefa;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Resultados extends Component
{
    /** @var array<string,array<int,array>> */
    public array $itensByTipo = ['T1' => [], 'T2' => [], 'T3' => [], 'T4' => []];

    /** @var array<string,int> */
    public array $qtd = ['T1' => 0, 'T2' => 0, 'T3' => 0, 'T4' => 0];

    public function mount(): void
    {
        $tarefas = Tarefa::query()
            ->whereIn('tipo', ['t1', 't2', 't3', 't4'])
            ->whereNotNull('finished_at')
            ->orderByDesc('id')
            ->with([
                'buscas:id,tarefa_id,q,slug,created_at',
                'videos:id,tarefa_id,canal_id,cod,nome,views,likes,comments,dt',
                'videos.canal:id,nome,youtube_id,inscritos,views,videos',
                'comentarios:id,tarefa_id,video_id,likes,dt,tox',
            ])
            ->get();

        foreach ($tarefas as $t) {
            $tipo = strtoupper($t->tipo); // t1 -> T1
            if (!isset($this->itensByTipo[$tipo])) continue;

            $card = match ($tipo) {
                'T1' => $this->mapT1($t),
                'T2' => $this->mapT2($t),
                'T3' => $this->mapT3($t),
                'T4' => $this->mapT4($t),
                default => null,
            };

            if ($card) $this->itensByTipo[$tipo][] = $card;
        }

        foreach (array_keys($this->itensByTipo) as $k) {
            $this->qtd[$k] = count($this->itensByTipo[$k]);
        }
    }

    private function baseHeader($t, string $tipo): array
    {
        $ini = $t->created_at ?? now();
        $fim = $t->finished_at ?? $t->updated_at ?? now();
        $duracaoSeg = $ini?->diffInSeconds($fim) ?? 0;

        return [
            'id'            => $t->id,
            'tipo'          => $tipo,
            'inicio'        => $ini?->format('d/m/Y H:i'),
            'fim'           => $fim?->format('d/m/Y H:i'),
            'duracao_seg'   => $duracaoSeg,
            'duracao_human' => $this->fmtDuracao($duracaoSeg),
        ];
    }

    private function mapT1($t): array
    {
        $header = $this->baseHeader($t, 'T1');

        $toxMedia = round((float) $t->comentarios->avg('tox'), 4);
        $toxPorVideo = $t->comentarios
            ->groupBy('video_id')
            ->map(fn($g) => round((float) $g->avg('tox'), 4))
            ->toArray();

        $canais = $t->videos
            ->pluck('canal')->filter()->unique('id')->values()
            ->map(fn($c) => [
                'id'        => $c->id,
                'nome'      => $c->nome,
                'inscritos' => $c->inscritos,
                'views'     => $c->views,
                'videos'    => $c->videos,
            ])->all();

        $buscas = $t->buscas->pluck('q')->filter()->values()->all();

        $videos = $t->videos->map(fn($v) => [
            'cod'      => $v->cod,
            'nome'     => $v->nome,
            'canal'    => $v->canal?->nome,
            'views'    => $v->views,
            'likes'    => $v->likes,
            'comments' => $v->comments,
            'dt'       => optional($v->dt)->format('d/m/Y'),
        ])->all();

        $comentarios = $t->comentarios
            ->sortBy('dt')
            ->map(fn($c) => [
                'video_id' => $c->video_id,
                'dt'       => optional($c->dt)->format('d/m/Y H:i'),
                'likes'    => $c->likes,
                'tox'      => $c->tox,
            ])->values()->all();


        $dados = $t->dados ?? [];
        $acertou  = (bool) data_get($dados, 'acertou');
        $feedback = trim((string) data_get($dados, 'feedback', ''));

        $toxMediaJson = data_get($dados, 'tox_media', []);
        $toxMediaJsonFmt = collect($toxMediaJson)
            ->map(fn($v) => round($v * 100, 1) . '%')
            ->toArray();

        return $header + [
            'payload' => [
                'acertou'        => $acertou,
                'feedback'       => $feedback,
                'buscas'         => $buscas,
                'videos'         => $videos,
                'canais'         => $canais,
                'comentarios'    => $comentarios, // <<< AQUI
                'comentarios_qt' => $t->comentarios->count(),
                'tox_media'      => $toxMedia,
                'tox_por_video'  => $toxPorVideo,
                'tox_json'       => $toxMediaJsonFmt,
                'dados_json'     => $dados,
            ],
        ];

    }

    private function mapT2($t): array
    {
        $header = $this->baseHeader($t, 'T2');

        $toxMedia = round((float) $t->comentarios->avg('tox'), 4);
        $toxPorVideo = $t->comentarios
            ->groupBy('video_id')
            ->map(fn($g) => round((float) $g->avg('tox'), 4))
            ->toArray();

        $canais = $t->videos
            ->pluck('canal')->filter()->unique('id')->values()
            ->map(fn($c) => [
                'id'        => $c->id,
                'nome'      => $c->nome,
                'inscritos' => $c->inscritos,
                'views'     => $c->views,
                'videos'    => $c->videos,
            ])->all();

        $buscas = $t->buscas->pluck('q')->filter()->values()->all();

        $videos = $t->videos->map(fn($v) => [
            'cod'      => $v->cod,
            'nome'     => $v->nome,
            'canal'    => $v->canal?->nome,
            'views'    => $v->views,
            'likes'    => $v->likes,
            'comments' => $v->comments,
            'dt'       => optional($v->dt)->format('d/m/Y'),
        ])->all();

        $dados = $t->dados ?? [];
        $acertou  = data_get($dados, 'acertou', null);
        $feedback = trim((string) data_get($dados, 'feedback', ''));

        $toxMediaJson = (array) data_get($dados, 'tox_media', []);
        $toxMediaJsonFmt = collect($toxMediaJson)
            ->map(fn($v) => is_numeric($v) ? round($v * 100, 1) . '%' : (string)$v)
            ->toArray();

        return $header + [
            'payload' => [
                'acertou'        => $acertou,
                'feedback'       => $feedback,
                'buscas'         => $buscas,
                'videos'         => $videos,
                'canais'         => $canais,
                'comentarios_qt' => $t->comentarios->count(),
                'tox_media'      => $toxMedia,
                'tox_por_video'  => $toxPorVideo,
                'tox_json'       => $toxMediaJsonFmt,
                'dados_json'     => $dados,
            ],
        ];
    }

    private function mapT3($t): array
    {
        $header = $this->baseHeader($t, 'T3');

        $dados    = $t->dados ?? [];
        $acertou  = data_get($dados, 'acertou', null);
        $feedback = (string) data_get($dados, 'feedback', '');
        $escolha  = data_get($dados, 'mais_Economizado');
        $vencedor = data_get($dados, 'mais_Economizado_real');
        $sel      = (array) data_get($dados, 'selecionados', []);

        $porCanal = $t->videos->groupBy('canal_id');

        $canais = [];
        foreach ($porCanal as $canalId => $lista) {
            $canal = optional($lista->first()->canal);

            $meta  = (array) data_get($sel, $canal->youtube_id ?? $canalId, []);
            $minFmt = data_get($meta, 'minutagemTotalFmt');
            $minTot = (int) data_get($meta, 'minutagemTotal', 0);
            if (!$minFmt && $minTot > 0) $minFmt = $this->fmtDuracao($minTot);

            $videos = $lista->map(fn($v) => [
                'cod'      => $v->cod,
                'nome'     => $v->nome,
                'views'    => $v->views,
                'likes'    => $v->likes,
                'comments' => $v->comments,
                'dt'       => optional($v->dt)->format('d/m/Y'),
            ])->all();

            $canais[] = [
                'channel_id'     => $canal->youtube_id ?? (string) $canalId,
                'nome'           => $canal->nome,
                'inscritos'      => $canal->inscritos,
                'views'          => $canal->views,
                'videos_qt'      => count($videos),
                'videos'         => $videos,
                'monetAvgUsd'    => (float) data_get($meta, 'monetAvgUsd', 0.0),
                'monthsBase'     => data_get($meta, 'monthsBase'),
                'areaUsd'        => data_get($meta, 'areaUsd'),
                'usdPerMin'      => data_get($meta, 'usdPerMin'),
                'minTotFmt'      => $minFmt,
            ];
        }

        return $header + [
            'payload' => [
                'acertou'   => $acertou,
                'feedback'  => trim($feedback),
                'escolha'   => $escolha,
                'vencedor'  => $vencedor,
                'canais'    => array_values($canais),
                'dados_json'=> $dados,
            ],
        ];
    }

    private function mapT4($t): array
    {
        // T4: “mesma coisa que as outras”
        // como você está usando T4 p/ canal/vídeos/comentários, dá pra reaproveitar o padrão T1/T2:
        $header = $this->baseHeader($t, 'T4');

        $toxMedia = round((float) $t->comentarios->avg('tox'), 4);
        $toxPorVideo = $t->comentarios
            ->groupBy('video_id')
            ->map(fn($g) => round((float) $g->avg('tox'), 4))
            ->toArray();

        $canais = $t->videos
            ->pluck('canal')->filter()->unique('id')->values()
            ->map(fn($c) => [
                'id'        => $c->id,
                'nome'      => $c->nome,
                'inscritos' => $c->inscritos,
                'views'     => $c->views,
                'videos'    => $c->videos,
            ])->all();

        $buscas = $t->buscas->pluck('q')->filter()->values()->all();

        $videos = $t->videos->map(fn($v) => [
            'cod'      => $v->cod,
            'nome'     => $v->nome,
            'canal'    => $v->canal?->nome,
            'views'    => $v->views,
            'likes'    => $v->likes,
            'comments' => $v->comments,
            'dt'       => optional($v->dt)->format('d/m/Y'),
        ])->all();

        $dados = $t->dados ?? [];
        $feedback = trim((string) data_get($dados, 'feedback', ''));

        return $header + [
            'payload' => [
                'feedback'       => $feedback,
                'buscas'         => $buscas,
                'videos'         => $videos,
                'canais'         => $canais,
                'comentarios_qt' => $t->comentarios->count(),
                'tox_media'      => $toxMedia,
                'tox_por_video'  => $toxPorVideo,
                'dados_json'     => $dados,
            ],
        ];
    }

    private function fmtDuracao(int $s): string
    {
        if ($s < 60) return "{$s}s";
        if ($s < 3600) return floor($s / 60) . "m " . ($s % 60) . "s";
        return floor($s / 3600) . "h " . floor(($s % 3600) / 60) . "m";
    }

    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.resultados');
    }
}
