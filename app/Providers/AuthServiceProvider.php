<?php

namespace App\Providers;

use App\Models\{User, Permission, Occurrences};
use App\Policies\OccurrencePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Occurrences::class => OccurrencePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerPolicies();

        try {
            $permissions = Permission::all();

            foreach ($permissions as $permission) {
                Gate::define($permission->name, function (User $user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            }

            Gate::define('owner', function (User $user, $object) {
                return $user->id === $object->user_id;
            });

            Gate::define('driver.panel', function (User $user) {
                return $user->isAdmin() || optional($user->driver)->id !== null;
            });

            Gate::before(function (User $user, $ability = null) {
                if ($ability === 'driver.panel') {
                    return null;
                }
                if ($user->isAdmin()) {
                    return true;
                }
            });
        } catch (\Exception $e) {
        }
    }
}
