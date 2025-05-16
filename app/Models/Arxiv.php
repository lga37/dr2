<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arxiv extends Model
{
    /** @use HasFactory<\Database\Factories\ArxivFactory> */
    use HasFactory;

    protected $fillable = ['canal_id','ts'];

    public function canal(){
        return $this->belongsTo(Canal::class);
    }
}
