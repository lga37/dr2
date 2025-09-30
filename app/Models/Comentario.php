<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comentario extends Model
{
    use HasFactory;

    // protected $fillable = ['user','dt','likes','texto','video_id'];

    protected $guarded = [];

    protected $casts = [
        'dt'  => 'datetime',
        'tox' => 'float',
    ];

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }


    public function scopeSearch($query, $search)
    {

        if ($search != '') {
            return $query->where('user', 'LIKE', '%' . $search . '%')
                #->orWhere('slug','LIKE','%'. $search .'%')
                #->orWhere('youtube_id','LIKE','%'. $search .'%')
            ;
        }
        return;
    }
}
