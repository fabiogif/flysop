<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'capital' => 'boolean',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
