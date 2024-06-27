<?php

namespace App\Providers;

use App\Services\Application\ApplicationService;
use App\Services\Application\ApplicationServiceInterface;
use App\Services\ApplicationModule\ApplicationModuleService;
use App\Services\ApplicationModule\ApplicationModuleServiceInterface;
use App\Services\Company\CompanyService;
use App\Services\Company\CompanyServiceInterface;
use App\Services\CompanyLanguage\CompanyLanguageService;
use App\Services\CompanyLanguage\CompanyLanguageServiceInterface;
use App\Services\CompanyTranslation\CompanyTranslationService;
use App\Services\CompanyTranslation\CompanyTranslationServiceInterface;
use App\Services\Customer\CustomerService;
use App\Services\Customer\CustomerServiceInterface;
use App\Services\CustomerVisit\CustomerVisitService;
use App\Services\CustomerVisit\CustomerVisitServiceInterface;
use App\Services\DataFilter\DataFilterService;
use App\Services\DataFilter\DataFilterServiceInterface;
use App\Services\EmailConfiguration\EmailConfigurationService;
use App\Services\EmailConfiguration\EmailConfigurationServiceInterface;
use App\Services\Language\LanguageService;
use App\Services\Language\LanguageServiceInterface;
use App\Services\Module\ModuleService;
use App\Services\Module\ModuleServiceInterface;
use App\Services\ModulePackage\ModulePackageService;
use App\Services\ModulePackage\ModulePackageServiceInterface;
use App\Services\ModulePackageModule\ModulePackageModuleService;
use App\Services\ModulePackageModule\ModulePackageModuleServiceInterface;
use App\Services\ModuleSetting\ModuleSettingService;
use App\Services\ModuleSetting\ModuleSettingServiceInterface;
use App\Services\Order\OrderByCustomerService;
use App\Services\Order\OrderByCustomerServiceInterface;
use App\Services\Order\OrderService;
use App\Services\Order\OrderServiceInterface;
use App\Services\Role\RoleService;
use App\Services\Role\RoleServiceInterface;
use App\Services\Table\TableService;
use App\Services\Table\TableServiceInterface;
use App\Services\TableField\TableFieldService;
use App\Services\TableField\TableFieldServiceInterface;
use App\Services\TableHelper\TableHelperService;
use App\Services\TableHelper\TableHelperServiceInterface;
use App\Services\TableIndex\TableIndexService;
use App\Services\TableIndex\TableIndexServiceInterface;
use App\Services\Translation\TranslationService;
use App\Services\Translation\TranslationServiceInterface;
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
    public function boot(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TableServiceInterface::class, TableService::class);
        $this->app->bind(TableFieldServiceInterface::class, TableFieldService::class);
        $this->app->bind(TableIndexServiceInterface::class, TableIndexService::class);
        $this->app->bind(ModuleServiceInterface::class, ModuleService::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
        $this->app->bind(ModuleSettingServiceInterface::class, ModuleSettingService::class);
        $this->app->bind(TableHelperServiceInterface::class, TableHelperService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(ApplicationServiceInterface::class, ApplicationService::class);
        $this->app->bind(EmailConfigurationServiceInterface::class, EmailConfigurationService::class);
        $this->app->bind(DataFilterServiceInterface::class, DataFilterService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(OrderByCustomerServiceInterface::class, OrderByCustomerService::class);
        $this->app->bind(ApplicationModuleServiceInterface::class, ApplicationModuleService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(CustomerVisitServiceInterface::class, CustomerVisitService::class);
        $this->app->bind(LanguageServiceInterface::class, LanguageService::class);
        $this->app->bind(TranslationServiceInterface::class, TranslationService::class);
        $this->app->bind(CompanyLanguageServiceInterface::class, CompanyLanguageService::class);
        $this->app->bind(CompanyTranslationServiceInterface::class, CompanyTranslationService::class);
        $this->app->bind(ModulePackageServiceInterface::class, ModulePackageService::class);
        $this->app->bind(ModulePackageModuleServiceInterface::class, ModulePackageModuleService::class);
    }
}
