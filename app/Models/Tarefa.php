<?php

// app/Models/Tarefa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarefa extends Model
{
    protected $guarded = [];


    protected $casts = [
        'dados'                 => 'array',
        'finished_at'           => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAbertas($q)
    {
        return $q->where('status', 0);
    }
    public function scopeFechadas($q)
    {
        return $q->where('status', 1);
    }

    public function buscas(): HasMany
    {
        return $this->hasMany(Busca::class);
    }
    public function canais(): HasMany
    {
        return $this->hasMany(Canal::class);
    }
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }
}
