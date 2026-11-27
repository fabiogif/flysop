<?php

namespace App\Models;

use App\Models\Traits\UserACLTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ClientResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserACLTrait;

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

    // Link aponta para a página pública de redefinição (o app não abre o token)
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ClientResetPasswordNotification($token));
    }

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
