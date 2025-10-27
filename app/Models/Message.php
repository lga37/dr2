<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //

    protected $guarded = [];

    protected $casts = ['meta' => 'array'];

    public function waMessages()
    {
        return $this->hasMany(WaMessage::class);
    }
}
