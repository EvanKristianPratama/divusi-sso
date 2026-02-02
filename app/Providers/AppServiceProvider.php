<?php

namespace App\Providers;

use App\Services\Firebase\FirebaseService;
use App\Services\SsoService;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseService::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'));

            return new FirebaseService($factory->createAuth());
        });

        $this->app->singleton(SsoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
