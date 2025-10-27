<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $guarded = [];


    public function thread()
    {                 // alias
        return $this->belongsTo(Message::class, 'message_id');
    }
}
