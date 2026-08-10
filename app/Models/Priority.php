<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'weight', 'color', 'default_sla_hours'];

    public function search($filter = null)
    {
        return $this->where('name', 'ILIKE', "%{$filter}%")
            ->orderBy('weight', 'desc')
            ->paginate(10);
    }

    public function occurrences()
    {
        return $this->hasMany(Occurrences::class, 'priority_id');
    }
}
