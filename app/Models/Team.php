<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id'];

    public function search($filter = null)
    {
        return $this->where('name', 'ILIKE', "%{$filter}%")->paginate(10);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}
