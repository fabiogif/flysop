<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function search($filter = null)
    {
        return $this->where('name', 'ILIKE', "%{$filter}%")->paginate(10);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
