<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'public_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            if (empty($survey->public_token)) {
                $survey->public_token = (string) Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class)->orderByDesc('submitted_at');
    }

    public function search($filter = null)
    {
        return $this->when($filter, function ($query) use ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('title', 'ILIKE', "%{$filter}%")
                    ->orWhere('description', 'ILIKE', "%{$filter}%");
            });
        })
            ->orderByDesc('updated_at')
            ->paginate();
    }

    public function publicUrl(): string
    {
        return route('public.surveys.show', $this->public_token);
    }
}
