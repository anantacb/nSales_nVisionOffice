<?php

namespace App\Providers;

use App\Repositories\Eloquent\Office\Table\TableFieldRepository;
use App\Repositories\Eloquent\Office\Table\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepository;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
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
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(TableFieldRepositoryInterface::class, TableFieldRepository::class);
    }
}
