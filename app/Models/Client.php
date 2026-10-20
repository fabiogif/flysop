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
        'username',
        'avatar_url',
        'bio',
        'website',
        'google_id',
        'apple_id',
        'email_verified_at',
        'marketing_opt_in',
        'terms_accepted_at',
        'email',
        'phone',
        'password',
        'tenant_id',
        'uuid',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
    ];

    public function occurrences()
    {
        return $this->hasMany(Occurrences::class, 'clients_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
