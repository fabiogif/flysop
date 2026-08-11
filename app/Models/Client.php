<?php

namespace App\Models;

use App\Models\Traits\UserACLTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, UserACLTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'tenant_id',
        'uuid',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
