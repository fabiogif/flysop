<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusOccurrence extends Model
{

    protected $fillable = ['id', 'name', 'is_terminal', 'sort_order'];
    use HasFactory;

    protected $casts = [
        'is_terminal' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function search($filtter = null)
    {
        $result = $this->where('name', 'LIKE', "%{$filtter}%")
            ->orderBy('sort_order')
            ->paginate(10);
        return $result;
    }

    public function occurrences()
    {
        $this->hasMany(Occurrences::class);

    }
}
