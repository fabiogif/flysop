<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Endereço para exibir a foto. No S3/Tigris o bucket é privado (fotos podem ter dados
     * pessoais), então gera URL assinada válida por 1 hora; no disco local, a URL do disco.
     */
    public static function urlFor(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk();

        return config('filesystems.disks.' . config('filesystems.default') . '.driver') === 's3'
            ? $disk->temporaryUrl($path, now()->addHour())
            : $disk->url($path);
    }

    public function getPublicUrlAttribute(): ?string
    {
        return self::urlFor($this->url);
    }
}
