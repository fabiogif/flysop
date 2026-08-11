<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Occurrences extends Model
{
    use LogsActivity;
    protected $fillable = ['protocol', 'title', 'name', 'description', 'cpf', 'rg', 'address', 'neighborhood', 'city', 'state', 'zip', 'users_id', 'email', 'issuings_id', 'type_occurrences_id', 'phone', 'latitude', 'longitude', 'status_occurrences_id', 'priority_id', 'due_at', 'driver_id', 'clients_id', 'nameType', 'nameStatus'];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    // Coluna gerada pelo Postgres (busca global — Fase 4), nunca setada via PHP.
    protected $hidden = ['search_vector'];

    use HasFactory;

    public function search($filter = null)
    {
        $result = $this->where('title', 'ILIKE', "%{$filter}%")
            ->orWhere('name', 'ILIKE', "%{$filter}%")
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')
            ->paginate(10);

        return $result;
    }

    /**
     * Busca global full-text (Fase 4): protocolo, título, nome e descrição, com relevância
     * (ts_rank). Usada pelo Admin\SearchController — não pela busca da listagem de
     * ocorrências (essa continua em Occurrence(), que usa ILIKE + full-text combinados).
     */
    public function scopeFullTextSearch($query, string $term)
    {
        return $query
            ->whereRaw("search_vector @@ plainto_tsquery('portuguese', ?)", [$term])
            ->orderByRaw("ts_rank(search_vector, plainto_tsquery('portuguese', ?)) DESC", [$term]);
    }


    public function Occurrence($filter = null)
    {

        $occurrences = $this->select(
            'occurrences.*',
            'issuings.id as idIssuings',
            'issuings.name as nameIssuings',
            'type_occurrences.id as idType',
            'type_occurrences.name as nameType',
            'status_occurrences_id as status_occurrences_id',
            'status_occurrences.name as nameStatus'
        )->join('issuings', 'issuings.id', '=', 'occurrences.issuings_id')
            ->join('status_occurrences', 'status_occurrences.id', '=', 'occurrences.status_occurrences_id')
            ->join('type_occurrences', function ($join) {
                $join->on('occurrences.type_occurrences_id', '=', 'type_occurrences.id');

            })->where(function ($queryFilter) use ($filter) {
            if ($filter) {
                $queryFilter->where('occurrences.title', 'ILIKE', "%{$filter}%")
                    ->orWhere('occurrences.name', 'ILIKE', "%{$filter}%")
                    ->orWhere('occurrences.protocol', 'ILIKE', "%{$filter}%")
                    ->orWhereRaw("occurrences.search_vector @@ plainto_tsquery('portuguese', ?)", [$filter]);
            }
        })->orderBy('occurrences.updated_at', 'desc')->orderBy('occurrences.created_at', 'desc')->paginate();

        return $occurrences;
    }

    public function OccurrenceByClientId($filter = null)
    {

        $occurrences = $this->select(
            'occurrences.*',
            'issuings.id as idIssuings',
            'issuings.name as nameIssuings',
            'type_occurrences.id as idType',
            'type_occurrences.name as nameType',
            'status_occurrences_id as status_occurrences_id',
            'status_occurrences.name as nameStatus'
        )->join('issuings', 'issuings.id', '=', 'occurrences.issuings_id')
            ->join('status_occurrences', 'status_occurrences.id', '=', 'occurrences.status_occurrences_id')
            ->join('type_occurrences', function ($join) {
                $join->on('occurrences.type_occurrences_id', '=', 'type_occurrences.id');

            })->where(function ($queryFilter) use ($filter) {
            if ($filter) {
                $queryFilter->where('occurrences.clients_id', '=', "{$filter}");
            }
        })->paginate();

        return $occurrences;
    }


    public function statusOccurence()
    {
        return $this->belongsTo(StatusOccurrence::class, 'status_occurrences_id');
    }

    public function typeOccurrence()
    {
        return $this->belongsTo(TypeOccurrence::class);
    }

    public function imagens()
    {
        return $this->hasMany(OccurrencesImagens::class, 'occurrence_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OccurrenceStatusHistory::class, 'occurrence_id')->orderByDesc('created_at');
    }

    /**
     * Auditoria (Fase 3): quem editou quais campos. status_occurrences_id fica de fora
     * porque já tem trilha própria e mais rica em occurrence_status_history (de/para,
     * quem mudou, nota) — não duplicar aqui.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'description', 'address', 'neighborhood', 'city', 'state', 'zip',
                'type_occurrences_id', 'issuings_id', 'priority_id', 'driver_id', 'due_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope: ocorrências visíveis pelo motorista (atribuídas a ele ou ao órgão informado).
     */
    public function scopeForDriver($query, $driverId, $issuingsId = null)
    {
        return $query->where(function ($q) use ($driverId, $issuingsId) {
            $q->where('driver_id', $driverId);
            if ($issuingsId !== null) {
                $q->orWhere('issuings_id', $issuingsId);
            }
        });
    }
}