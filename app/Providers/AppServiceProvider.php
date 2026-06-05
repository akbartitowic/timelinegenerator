<?php

namespace App\Providers;

use App\Services\WorkingDaysService;
use App\Services\DependencyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkingDaysService::class);
        $this->app->singleton(DependencyService::class);
    }

    public function boot(): void
    {
        //
    }
}
