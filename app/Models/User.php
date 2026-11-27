<?php

namespace App\Models;

use App\Models\Traits\UserACLTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserACLTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'issuing_id',
        'notify_new_occurrences',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notify_new_occurrences' => 'boolean',
    ];



    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function issuing()
    {
        return $this->belongsTo(Issuing::class);
    }

    /**
     * Gestor = usuário vinculado a um órgão: acessa pelo app só as ocorrências desse órgão
     * e recebe aviso das novas (ver OccurrencePolicy e NewOccurrenceForIssuingNotification).
     */
    public function isManager(): bool
    {
        return $this->issuing_id !== null;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


    public function rolesAvailable($filter = null)
    {

        $roles = Role::whereNotIn('roles.id', function ($query) {
            $query->select('role_user.role_id');
            $query->from('role_user');
            $query->whereRaw("role_user.user_id={$this->id}");
        })->where(function ($queryFilter) use ($filter) {
            if ($filter) {
                $queryFilter->where('roles.name', 'LIKE', "%{$filter}%");
            }
        })->paginate();

        return $roles;
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTenantUser($query)
    {
        return $query->where('tenant_id', auth()->user()->tenant_id);
    }
}
