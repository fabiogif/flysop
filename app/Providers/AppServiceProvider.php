<?php

namespace App\Providers;

use App\Models\{Category, Occurrences, Product, Tenant};
use App\Observers\CategoryObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Observers\ProductObserver;
use App\Observers\OccurrenceObserver;
use App\Observers\TenantObserver;
use App\Repositories\Contracts\OccurrenceRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Contracts\TypeOccurrenceRepositoryInterface;
use App\Repositories\OccurrenceRepository;
use App\Repositories\TenantRepository;
use App\Repositories\TypeOccurrenceRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            TenantRepositoryInterface::class ,
            TenantRepository::class ,
        );
        $this->app->bind(
            OccurrenceRepositoryInterface::class ,
            OccurrenceRepository::class
        );
        $this->app->bind(
            TypeOccurrenceRepositoryInterface::class ,
            TypeOccurrenceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }

        Paginator::useBootstrap();
        Tenant::observe(TenantObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        Occurrences::observe(OccurrenceObserver::class);

        Blade::if ('admin', function () {
            $user = auth()->user();
            return $user->isAdmin();
        });
    }
}
