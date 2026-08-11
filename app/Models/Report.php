<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';

    public const TYPE_OCCURRENCES = 'occurrences';
    public const TYPE_STATUS_DURATIONS = 'status_durations';

    protected $fillable = ['user_id', 'type', 'status', 'filters', 'file_path', 'error', 'ready_at'];

    protected $casts = [
        'filters' => 'array',
        'ready_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_OCCURRENCES => 'Listagem de ocorrências',
            self::TYPE_STATUS_DURATIONS => 'Tempo médio por etapa',
        ];
    }
}
