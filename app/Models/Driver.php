<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    public const STATUS_DISPONIVEL = 'disponivel';
    public const STATUS_EM_DESLOCAMENTO = 'em_deslocamento';
    public const STATUS_EM_ATENDIMENTO = 'em_atendimento';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf',
        'status',
        'tenant_id',
        'user_id',
        'team_id',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'team_id' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function occurrences()
    {
        return $this->hasMany(Occurrences::class, 'driver_id');
    }

    public function positions()
    {
        return $this->hasMany(DriverPosition::class);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DISPONIVEL => 'Disponível',
            self::STATUS_EM_DESLOCAMENTO => 'Em deslocamento',
            self::STATUS_EM_ATENDIMENTO => 'Em atendimento',
        ];
    }
}
