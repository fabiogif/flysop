<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccurrencesImagens extends Model
{
    public const PHASE_ANTES = 'antes';
    public const PHASE_DEPOIS = 'depois';

    protected $fillable = [
        'occurrence_id',
        'url',
        'uploaded_by_user_id',
        'phase',
        'latitude',
        'longitude',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'captured_at' => 'datetime',
    ];

    use HasFactory;

    public function occurrence()
    {
        return $this->belongsTo(Occurrences::class, 'occurrence_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
