<?php

namespace App\Providers;

use App\Repositories\Eloquent\Office\Company\CompanyRepository;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\CompanyModuleRepository;
use App\Repositories\Eloquent\Office\Module\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepository;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Setting\ModuleSettingRepository;
use App\Repositories\Eloquent\Office\Setting\ModuleSettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Setting\SettingRepository;
use App\Repositories\Eloquent\Office\Setting\SettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\CompanyTableFieldRepository;
use App\Repositories\Eloquent\Office\Table\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\CompanyTableRepository;
use App\Repositories\Eloquent\Office\Table\CompanyTableRepositoryInterface;
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
        $this->app->bind(ModuleRepositoryInterface::class, ModuleRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(TableFieldRepositoryInterface::class, TableFieldRepository::class);
        $this->app->bind(CompanyTableFieldRepositoryInterface::class, CompanyTableFieldRepository::class);
        $this->app->bind(CompanyTableRepositoryInterface::class, CompanyTableRepository::class);
        $this->app->bind(ModuleSettingRepositoryInterface::class, ModuleSettingRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(CompanyModuleRepositoryInterface::class, CompanyModuleRepository::class);
    }
}
