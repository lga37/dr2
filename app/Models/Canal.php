<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canal extends Model
{
    use HasFactory;

    // protected $fillable = ['nome','slug','busca_id',
    // 'dt','local','categ',
    // 'videos','desc','score','inscritos','views','min','max','engagement','frequency','length'];

    protected $guarded = [];


    protected function casts(): array
    {
        return [
            'dt' => 'date:Y-m-d',
        ];
    }
    
    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function arxivs()
    {
        return $this->hasMany(Arxiv::class);
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
                ->orWhere('youtube_id', 'LIKE', '%' . $search . '%')
            ;
        }
        return;
    }
}
