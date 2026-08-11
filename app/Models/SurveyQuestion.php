<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    public const TYPE_TEXT = 'text';
    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_SCALE = 'scale';

    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_SINGLE_CHOICE,
        self::TYPE_SCALE,
    ];

    protected $fillable = [
        'survey_id',
        'type',
        'prompt',
        'options',
        'required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_TEXT => 'Texto livre',
            self::TYPE_SINGLE_CHOICE => 'Múltipla escolha (única)',
            self::TYPE_SCALE => 'Escala 1–5',
        ];
    }
}
