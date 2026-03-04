<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;
use App\Repositories\ConductorRepository;
use App\Repositories\CiudadanoRepository;
use App\Services\UserService;
use App\Services\AuthService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(ConductorRepository::class);
        $this->app->singleton(CiudadanoRepository::class);
        
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepository::class),
                $app->make(ConductorRepository::class),
                $app->make(CiudadanoRepository::class)
            );
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(UserRepository::class)
            );
        });
    }
}