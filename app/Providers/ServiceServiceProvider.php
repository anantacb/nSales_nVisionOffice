<?php

namespace App\Providers;

use App\Services\Company\CompanyService;
use App\Services\Company\CompanyServiceInterface;
use App\Services\Module\ModuleService;
use App\Services\Module\ModuleServiceInterface;
use App\Services\ModuleSetting\ModuleSettingService;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use App\Services\Table\TableService;
use App\Services\Table\TableServiceInterface;
use App\Services\TableField\TableFieldService;
use App\Services\TableField\TableFieldServiceInterface;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
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
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TableServiceInterface::class, TableService::class);
        $this->app->bind(TableFieldServiceInterface::class, TableFieldService::class);
        $this->app->bind(ModuleServiceInterface::class, ModuleService::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
        $this->app->bind(ModuleSettingServiceInterface::class, ModuleSettingService::class);
    }
}
