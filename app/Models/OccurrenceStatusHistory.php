<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OccurrenceStatusHistory extends Model
{
    protected $table = 'occurrence_status_history';

    protected $fillable = ['occurrence_id', 'from_status_id', 'to_status_id', 'changed_by_user_id', 'note'];

    public function occurrence()
    {
        return $this->belongsTo(Occurrences::class, 'occurrence_id');
    }

    public function fromStatus()
    {
        return $this->belongsTo(StatusOccurrence::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(StatusOccurrence::class, 'to_status_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
