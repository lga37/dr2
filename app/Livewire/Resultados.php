<?php

namespace App\Livewire;

use App\Models\Tarefa;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class Resultados extends Component
{
    /** @var array<string,array> */
    public array $itensByTipo = ['T1' => [], 'T2' => [], 'T3' => []];

    /** @var array<string,int> */
    public array $qtd = ['T1' => 0, 'T2' => 0, 'T3' => 0];

    public function mount(): void
    {
        $userId = Auth::id();

        $tarefas = Tarefa::query()
            ->where('user_id', $userId)
            ->whereIn('tipo', ['t1', 't2', 't3'])        // <- normaliza para minúsculo
            ->whereNotNull('finished_at')               // <- só concluídas
            ->orderByDesc('id')
            ->with([
                'buscas:id,tarefa_id,q,slug,created_at',
                'videos:id,tarefa_id,canal_id,cod,nome,views,likes,comments,dt',
                'videos.canal:id,nome,youtube_id,inscritos,views,videos',
                'comentarios:id,tarefa_id,video_id,likes,dt,tox',
            ])
            ->get();

        // agrupa e mapeia por tipo
        foreach ($tarefas as $t) {
            $tipo = strtoupper($t->tipo); // 't1' -> 'T1'
            if (!in_array($tipo, ['T1', 'T2', 'T3'], true)) continue;

            $card = match ($tipo) {
                'T1' => $this->mapT1($t),
                'T2' => $this->mapT2($t),
                'T3' => $this->mapT3($t),
            };

            $this->itensByTipo[$tipo][] = $card;
        }

        // contadores para as abas
        foreach (['T1', 'T2', 'T3'] as $k) {
            $this->qtd[$k] = count($this->itensByTipo[$k]);
        }
    }

    private function mapT1($t): array
    {
        $fim = $t->finished_at ?? $t->updated_at ?? now();
        $duracaoSeg = $t->created_at?->diffInSeconds($fim) ?? 0;

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
        $acertou  = (bool) data_get($dados, 'acertou');
        $feedback = trim((string) data_get($dados, 'feedback', ''));
        $toxMediaJson = data_get($dados, 'tox_media', []);
        $toxMediaJsonFmt = collect($toxMediaJson)
            ->map(fn($v) => round($v * 100, 1) . '%')
            ->toArray();

        return [
            'id'             => $t->id,
            'quando'         => $t->created_at?->format('d/m/Y H:i'),
            'duracao_seg'    => $duracaoSeg,
            'duracao_human'  => $this->fmtDuracao($duracaoSeg),
            'acertou'        => $acertou,
            'feedback'       => $feedback,
            'buscas'         => $buscas,
            'videos'         => $videos,
            'canais'         => $canais,
            'comentarios_qt' => $t->comentarios->count(),
            'tox_media'      => $toxMedia,
            'tox_por_video'  => $toxPorVideo,
            'tox_json'       => $toxMediaJsonFmt,
            'tipo'           => 'T1',
        ];
    }

    private function mapT2($t): array
    {
        $fim = $t->finished_at ?? $t->updated_at ?? now();
        $duracaoSeg = $t->created_at?->diffInSeconds($fim) ?? 0;

        return [
            'id'            => $t->id,
            'quando'        => $t->created_at?->format('d/m/Y H:i'),
            'duracao_human' => $this->fmtDuracao($duracaoSeg),
            'feedback'      => (string) data_get($t->dados, 'feedback', ''),
            // chaves “genéricas” para o card
            'acertou'       => data_get($t->dados, 'acertou', null),   // pode ser null
            'snapshot'      => (array) data_get($t->dados, 'tox_media', []),
            'tipo'          => 'T2',
        ];
    }

    private function mapT3($t): array
    {
        $fim = $t->finished_at ?? $t->updated_at ?? now();
        $duracaoSeg = $t->created_at?->diffInSeconds($fim) ?? 0;

        return [
            'id'            => $t->id,
            'quando'        => $t->created_at?->format('d/m/Y H:i'),
            'duracao_human' => $this->fmtDuracao($duracaoSeg),
            'feedback'      => (string) data_get($t->dados, 'feedback', ''),
            // chaves “genéricas” para o card
            'acertou'       => data_get($t->dados, 'acertou', null),
            'snapshot'      => (array) data_get($t->dados, 'tox_media', []),
            'tipo'          => 'T3',
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
