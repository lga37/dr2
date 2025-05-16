<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    // protected $fillable = ['nome','slug','canal_id','busca_id',
    //     'desc','dt','lang','categ_id','views','likes','dislikes','favorites','comments','duration','caption',
    // ];
    protected $guarded = [];

    # neste caso coloquei comments para nao dar pau aqui na table
    public function comentarios(){
        return $this->hasMany(Comentario::class);
    }

    public function busca(){
        return $this->belongsTo(Busca::class);
    }

    public function scopeSearch($query, $search){

        if($search != ''){
            return $query->where('nome','LIKE','%'. $search .'%')
            ->orWhere('slug','LIKE','%'. $search .'%')
            ;           
    
        }
        return;
    }




}
