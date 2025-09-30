<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

   
    protected $guarded = [];

    // app/Models/Video.php
    protected $casts = [
        'hashtags' => 'array',
        'dt'       => 'datetime',
    ];

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class);
    }

    # neste caso coloquei comments para nao dar pau aqui na table
    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function busca()
    {
        return $this->belongsTo(Busca::class);
    }

    public function scopeSearch($query, $search)
    {

        if ($search != '') {
            return $query->where('nome', 'LIKE', '%' . $search . '%')
                ->orWhere('slug', 'LIKE', '%' . $search . '%')
            ;
        }
        return;
    }
}
