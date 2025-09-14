<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Repositories\WalletRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */


    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\WalletRepositoryInterface::class,
            \App\Repositories\WalletRepository::class
        );
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}
