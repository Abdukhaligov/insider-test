<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\SeasonRepositoryInterface::class,
            \App\Repositories\SeasonRepository::class
        ); 
        
        $this->app->bind(
            \App\Repositories\TeamRepositoryInterface::class,
            \App\Repositories\TeamRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\MatchRepositoryInterface::class,
            \App\Repositories\MatchRepository::class
        );
        
        $this->app->bind(
            \App\Services\MatchOrganizerServiceInterface::class,
            \App\Services\MatchOrganizerService::class
        );

        $this->app->bind(
            \App\Services\SimulateServiceInterface::class,
            \App\Services\SimulateService::class
        );

        $this->app->bind(
            \App\Services\SeasonServiceInterface::class,
            \App\Services\SeasonService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
