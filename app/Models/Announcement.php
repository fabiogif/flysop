<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'issuing_id',
        'created_by_user_id',
        'title',
        'body',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function issuing()
    {
        return $this->belongsTo(Issuing::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Visíveis ao público (site e app): publicados, do tenant, mais recentes primeiro.
     */
    public function scopePublishedFor($query, int $tenantId, ?int $issuingId = null)
    {
        return $query->where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->when($issuingId, fn ($q) => $q->where('issuing_id', $issuingId))
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
