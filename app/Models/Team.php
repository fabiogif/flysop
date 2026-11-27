<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id', 'issuing_id', 'type_occurrences_id'];

    public function search($filter = null)
    {
        return $this->with('department')->where('name', 'ILIKE', "%{$filter}%")->paginate(10);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Secretaria (órgão) da equipe (Eixo 4). Nulo = equipe compartilhada entre secretarias.
     */
    public function issuing()
    {
        return $this->belongsTo(Issuing::class);
    }

    /**
     * Equipes que podem receber ocorrências de um órgão: as dele e as compartilhadas.
     */
    public function scopeAvailableForIssuing($query, ?int $issuingId)
    {
        return $query->where(fn ($q) => $q->whereNull('issuing_id')->orWhere('issuing_id', $issuingId));
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * Especialidade da equipe (Fase 5): tipo de ocorrência que ela atende. Nulo = qualquer tipo.
     */
    public function typeOccurrence()
    {
        return $this->belongsTo(TypeOccurrence::class, 'type_occurrences_id');
    }
}
