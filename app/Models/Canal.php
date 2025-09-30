<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Canal extends Model
{
    use HasFactory;

    protected $guarded = [];


    // protected function casts(): array
    // {
    //     return [
    //         'dt' => 'date:Y-m-d',
    //     ];
    // }


    protected $casts = [
        'keywords'      => 'array',
        'dt'         => 'date',
    ];



    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }
    public function busca(): BelongsTo
    {
        return $this->belongsTo(Busca::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function arxivs()
    {
        return $this->hasMany(Arxiv::class);
    }



    public function scopeSearch($query, $search)
    {

        if ($search != '') {
            return $query->where('nome', 'LIKE', '%' . $search . '%')
                ->orWhere('slug', 'LIKE', '%' . $search . '%')
                ->orWhere('youtube_id', 'LIKE', '%' . $search . '%')
            ;
        }
        return;
    }
}
