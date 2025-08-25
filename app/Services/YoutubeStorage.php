<?php
// app/Services/YoutubeStorage.php
namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\{Busca, Canal, Video, Comentario, Monet, Arxiv};

class YoutubeStorage
{


    protected function toDate(?string $v): ?string
    {
        if (empty($v)) return null;
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

  

    // --- Buscas ---
    public function upsertBusca(string $q, array $filters = []): Busca
    {
        $slug = Str::slug($q);
        return Busca::updateOrCreate(
            ['slug' => $slug],
            ['q' => $q, 'parse' => false]
        );
    }

    public function upsertCanal(array $ch, ?Busca $busca = null): Canal
    {
        $base = [
            'nome'       => $ch['nome'] ?? null,
            'slug'       => isset($ch['nome']) ? Str::slug($ch['nome']) : null,
            'youtube_id' => $ch['youtube_id'] ?? null,
            'desc'       => $ch['desc'] ?? null,
            'links'      => $ch['links'] ?? null,            // Model tem casts['links'=>'array']
            'inscritos'  => isset($ch['inscritos']) ? (int)$ch['inscritos'] : null,
            'views'      => isset($ch['views']) ? (int)$ch['views'] : null,
            'videos'     => isset($ch['videos']) ? (int)$ch['videos'] : null,
            'dt'         => $this->toDate($ch['dt'] ?? null), // <- DATE
            'local'      => $ch['local'] ?? null,
            'categ'      => $ch['categ'] ?? null,
            'score'      => $ch['score'] ?? null,
            'min'        => $ch['min'] ?? null,
            'max'        => $ch['max'] ?? null,
            'engagement' => $ch['engagement'] ?? null,
            'frequency'  => $ch['frequency'] ?? null,
            'length'     => $ch['length'] ?? null,
        ];
        if ($busca) $base['busca_id'] = $busca->id;

        return Canal::updateOrCreate(['cod' => $ch['cod']], $base);
    }

    public function upsertVideo(Canal $canal, array $v, ?Busca $busca = null): Video
    {
        $data = [
            'nome'       => $v['nome'] ?? null,
            'slug'       => isset($v['nome']) ? Str::slug($v['nome']) : null,
            'desc'       => $v['desc'] ?? null,
            'caption'    => $v['caption'] ?? null,
            'keywords'   => $v['keywords'] ?? null,                  // model: casts['keywords'=>'array']
            'comments'   => isset($v['comments']) ? (int)$v['comments'] : null,
            'likes'      => isset($v['likes']) ? (int)$v['likes'] : null,
            'dislikes'   => isset($v['dislikes']) ? (int)$v['dislikes'] : null,
            'views'      => isset($v['views']) ? (int)$v['views'] : null,
            'favorites'  => isset($v['favorites']) ? (int)$v['favorites'] : null,
            'duration'   => isset($v['duration']) ? (int)$v['duration'] : null,
            'categ_id'   => $v['categ_id'] ?? null,
            'lang'       => $v['lang'] ?? null,
            'dt'         => $this->toDateTime($v['dt'] ?? null),      // <- DATETIME
            'canal_id'   => $canal->id,
        ];
        if ($busca) $data['busca_id'] = $busca->id;

        return Video::updateOrCreate(['cod' => $v['cod']], $data);
    }


    
protected function toDateTime(?string $v): ?string {
    if (!$v) return null;
    try { return Carbon::parse($v)->toDateTimeString(); } catch (\Throwable) { return null; }
}

public function upsertComentarios(\App\Models\Video $video, array $items): void
{
   
    

    // ------ SEM 'cod' (seu caso atual) ------
    // chave = (video_id, user, dt). Se dt vier vazio, cai para (video_id, user, texto[0..191])
    foreach ($items as $c) {
        $dt   = $this->toDateTime($c['dt'] ?? null);
        $user = $c['user'] ?? null;
        $texto = $c['texto'] ?? null;

        $where = ['video_id' => $video->id, 'user' => $user];
        if ($dt) {
            $where['dt'] = $dt;
        } else {
            // fallback quando a API não retorna dt
            $where['texto'] = $texto ? mb_substr($texto, 0, 191) : null;
        }

        Comentario::updateOrCreate(
            $where,
            [
                'texto'    => $texto,
                'likes'    => $c['likes'] ?? null,
                'dislikes' => $c['dislikes'] ?? null,
                'tox'      => $c['tox'] ?? null,
            ]
        );
    }
}


    // --- Monet (séries VidIQ) ---
    public function upsertMonet(Canal $canal, \DateTimeInterface $dt, int $vlr, ?string $obs = null): Monet
    {
        return Monet::updateOrCreate(
            ['canal_id' => $canal->id, 'dt' => $dt],
            ['vlr' => $vlr, 'obs' => $obs]
        );
    }

    // --- Arxiv (Wayback série histórica) ---
    public function upsertArxiv(Canal $canal, \DateTimeInterface $ts, array $m): Arxiv
    {
        return Arxiv::updateOrCreate(
            ['canal_id' => $canal->id, 'ts' => $ts],
            ['views' => $m['views'] ?? null, 'subscribers' => $m['subscribers'] ?? null, 'url' => $m['url'] ?? null, 'obs' => $m['obs'] ?? null, 'parsed' => $m['parsed'] ?? false]
        );
    }
}
