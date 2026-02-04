<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccurrencesImagens extends Model
{
    protected $fillable = ['occurrence_id', 'url'];
    use HasFactory;


    public function Occurrences()
    {
        $this->belongsTo(Occurrences::class);
    }


}
