<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuing extends Model
{

    protected $fillable = ['id', 'name'];
    use HasFactory;

    public function search($filtter = null)
    {
        $result = $this->where('name', 'LIKE', "%{$filtter}%")
            ->paginate(10);
        return $result;
    }
}
