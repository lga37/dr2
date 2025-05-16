<?php

namespace App\Models;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Busca extends Model
{
    use HasFactory;

    protected $fillable = ['q','slug'];

    public function videos(){
        return $this->hasMany(Video::class);
    }

    public function canals(){
        return $this->hasMany(Canal::class);
    }

    // public function videos_count(){
    //     $videos = Video::withCount('videos')->get();
    //     return $videos->count();
    // }

    

}
