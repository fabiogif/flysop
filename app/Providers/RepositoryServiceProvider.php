<?php

namespace App\Providers;

use App\Repositories\{
    ClientConsumerRepository,
    ClientRepository,
    TenantRepository,
    OccurrenceRepository,
    TypeOccurrenceRepository,
    DriverRepository,
};
use App\Repositories\Contracts\{
    ClientConsumerRepositoryInterface,
    ClientRepositoryInterface,
    OccurrenceRepositoryInterface,
    TenantRepositoryInterface,
    TypeOccurrenceRepositoryInterface,
    DriverRepositoryInterface,
};
use App\Services\DashboardService;
use App\Services\Contracts\DashboardServiceInterface;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            TenantRepositoryInterface::class,
            TenantRepository::class
        );
        $this->app->bind(
            OccurrenceRepositoryInterface::class,
            OccurrenceRepository::class
        );
        $this->app->bind(
            ClientRepositoryInterface::class,
            ClientRepository::class
        );
        $this->app->bind(
            ClientConsumerRepositoryInterface::class,
            ClientConsumerRepository::class
        );
        $this->app->bind(
            TypeOccurrenceRepositoryInterface::class,
            TypeOccurrenceRepository::class
        );
        $this->app->bind(
            DashboardServiceInterface::class,
            DashboardService::class
        );
        $this->app->bind(
            DriverRepositoryInterface::class,
            DriverRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
