<?php

namespace App\Livewire;

use App\Models\Tarefa;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class Resultados extends Component
{
    public array $itens = []; // dataset pronto pra view

    public function mount(): void
    {
        $userId = Auth::id();

        // todas as T1 finalizadas do usuário (mais recente primeiro)
        $tarefas = Tarefa::query()
            ->where('user_id', $userId)
            ->where('tipo', 'T1')
            ->orderByDesc('id')
            ->with([
                // eager loading enxuto
                'buscas:id,tarefa_id,q,slug,created_at',
                'videos:id,tarefa_id,canal_id,cod,nome,views,likes,comments,dt',
                'videos.canal:id,nome,youtube_id,inscritos,views,videos',
                'comentarios:id,tarefa_id,video_id,likes,dt,tox',
            ])
            ->get();

        $this->itens = $tarefas->map(function ($t) {
            // duração em segundos (se ainda não houver finished_at, usa updated_at)
            $fim = $t->finished_at ?? $t->updated_at ?? now();
            $duracaoSeg = $t->created_at?->diffInSeconds($fim) ?? 0;

            // tox média geral da tarefa
            $toxMedia = round((float) $t->comentarios->avg('tox'), 4);

            // tox média por vídeo (video_id => média)
            $toxPorVideo = $t->comentarios
                ->groupBy('video_id')
                ->map(fn($g) => round((float) $g->avg('tox'), 4))
                ->toArray();

            // canais únicos a partir dos vídeos
            $canais = $t->videos
                ->pluck('canal')               // coleção de canais (pode ter null)
                ->filter()                     // remove null
                ->unique('id')
                ->values()
                ->map(fn($c) => [
                    'id'        => $c->id,
                    'nome'      => $c->nome,
                    'inscritos' => $c->inscritos,
                    'views'     => $c->views,
                    'videos'    => $c->videos,
                ])->all();

            // buscas (strings) pra exibir como chips
            $buscas = $t->buscas->pluck('q')->filter()->values()->all();

            // vídeos resumidos
            $videos = $t->videos->map(function ($v) {
                return [
                    'cod'      => $v->cod,
                    'nome'     => $v->nome,
                    'canal'    => $v->canal?->nome,
                    'views'    => $v->views,
                    'likes'    => $v->likes,
                    'comments' => $v->comments,
                    'dt'       => optional($v->dt)->format('d/m/Y'),
                ];
            })->all();

            // dados do JSON (acertou, feedback, tox_media real que você gravou)
            $dados = $t->dados ?? [];
            $acertou  = (bool) data_get($dados, 'acertou');
            $feedback = trim((string) data_get($dados, 'feedback', ''));
            $toxMediaJson = data_get($dados, 'tox_media', []); // array videoId=>valor
            // transforma em "videoId => 12.3%"
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
                'tox_media'      => $toxMedia,          // 0..1
                'tox_por_video'  => $toxPorVideo,       // id=>0..1
                'tox_json'       => $toxMediaJsonFmt,   // id=>'12.3%'
            ];
        })->all();
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
