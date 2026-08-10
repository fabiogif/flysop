<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOccurrence extends Model
{

    protected $fillable = ['id', 'name', 'sla_hours', 'parent_id'];
    use HasFactory;

    public function search($filtter = null)
    {
        $result = $this->where('name', 'LIKE', "%{$filtter}%")
            ->paginate(10);
        return $result;
    }

    public function parent()
    {
        return $this->belongsTo(TypeOccurrence::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TypeOccurrence::class, 'parent_id');
    }
}
