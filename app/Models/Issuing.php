<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuing extends Model
{

    /** Órgãos padrão da prefeitura (IssuingsTableSeeder). */
    public const DEFAULT_NAMES = [
        'Secretaria de Infraestrutura',
        'Secretaria de Saúde',
        'Secretaria de Educação',
        'Secretaria de Meio Ambiente',
        'Secretaria de Transporte',
        'Secretaria de Serviços Públicos',
        'Defesa Civil',
    ];

    protected $fillable = ['id', 'name'];
    use HasFactory;

    public function search($filtter = null)
    {
        $result = $this->where('name', 'LIKE', "%{$filtter}%")
            ->paginate(10);
        return $result;
    }

    public function managers()
    {
        return $this->hasMany(User::class);
    }
}
