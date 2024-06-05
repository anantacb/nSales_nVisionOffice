<?php

namespace App\Providers;

use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepository;
use App\Repositories\Eloquent\Company\CompanyLanguage\CompanyLanguageRepositoryInterface;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepository;
use App\Repositories\Eloquent\Company\CompanyTranslation\CompanyTranslationRepositoryInterface;
use App\Repositories\Eloquent\Company\Customer\CustomerRepository;
use App\Repositories\Eloquent\Company\Customer\CustomerRepositoryInterface;
use App\Repositories\Eloquent\Company\CustomerVisit\CustomerVisitRepository;
use App\Repositories\Eloquent\Company\CustomerVisit\CustomerVisitRepositoryInterface;
use App\Repositories\Eloquent\Company\Item\ItemRepository;
use App\Repositories\Eloquent\Company\Item\ItemRepositoryInterface;
use App\Repositories\Eloquent\Company\ItemAttribute\ItemAttributeRepository;
use App\Repositories\Eloquent\Company\ItemAttribute\ItemAttributeRepositoryInterface;
use App\Repositories\Eloquent\Company\Order\OrderLineRepository;
use App\Repositories\Eloquent\Company\Order\OrderLineRepositoryInterface;
use App\Repositories\Eloquent\Company\Order\OrderRepository;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopLanguage\WebShopLanguageRepository;
use App\Repositories\Eloquent\Company\WebShopLanguage\WebShopLanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Application\ApplicationRepository;
use App\Repositories\Eloquent\Office\Application\ApplicationRepositoryInterface;
use App\Repositories\Eloquent\Office\ApplicationModule\ApplicationModuleRepository;
use App\Repositories\Eloquent\Office\ApplicationModule\ApplicationModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\Company\CompanyRepository;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepository;
use App\Repositories\Eloquent\Office\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepository;
use App\Repositories\Eloquent\Office\CompanyTable\CompanyTableRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableField\CompanyTableFieldRepository;
use App\Repositories\Eloquent\Office\CompanyTableField\CompanyTableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyTableIndex\CompanyTableIndexRepository;
use App\Repositories\Eloquent\Office\CompanyTableIndex\CompanyTableIndexRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepository;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepository;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\DataFilter\DataFilterRepository;
use App\Repositories\Eloquent\Office\DataFilter\DataFilterRepositoryInterface;
use App\Repositories\Eloquent\Office\EmailConfiguration\EmailConfigurationRepository;
use App\Repositories\Eloquent\Office\EmailConfiguration\EmailConfigurationRepositoryInterface;
use App\Repositories\Eloquent\Office\Language\LanguageRepository;
use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepository;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepository;
use App\Repositories\Eloquent\Office\ModulePackage\ModulePackageRepositoryInterface;
use App\Repositories\Eloquent\Office\ModuleSetting\ModuleSettingRepository;
use App\Repositories\Eloquent\Office\ModuleSetting\ModuleSettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepository;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use App\Repositories\Eloquent\Office\Setting\SettingRepository;
use App\Repositories\Eloquent\Office\Setting\SettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Table\TableRepository;
use App\Repositories\Eloquent\Office\Table\TableRepositoryInterface;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepository;
use App\Repositories\Eloquent\Office\TableField\TableFieldRepositoryInterface;
use App\Repositories\Eloquent\Office\TableIndex\TableIndexRepository;
use App\Repositories\Eloquent\Office\TableIndex\TableIndexRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepository;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepository;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
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
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(CompanyTableRepositoryInterface::class, CompanyTableRepository::class);

        $this->app->bind(TableFieldRepositoryInterface::class, TableFieldRepository::class);
        $this->app->bind(CompanyTableFieldRepositoryInterface::class, CompanyTableFieldRepository::class);

        $this->app->bind(TableIndexRepositoryInterface::class, TableIndexRepository::class);
        $this->app->bind(CompanyTableIndexRepositoryInterface::class, CompanyTableIndexRepository::class);

        $this->app->bind(ModuleRepositoryInterface::class, ModuleRepository::class);
        $this->app->bind(CompanyModuleRepositoryInterface::class, CompanyModuleRepository::class);
        $this->app->bind(ModulePackageRepositoryInterface::class, ModulePackageRepository::class);

        $this->app->bind(ModuleSettingRepositoryInterface::class, ModuleSettingRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);

        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CompanyUserRepositoryInterface::class, CompanyUserRepository::class);

        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(CompanyUserRoleRepositoryInterface::class, CompanyUserRoleRepository::class);

        $this->app->bind(ApplicationRepositoryInterface::class, ApplicationRepository::class);
        $this->app->bind(ApplicationModuleRepositoryInterface::class, ApplicationModuleRepository::class);

        $this->app->bind(EmailConfigurationRepositoryInterface::class, EmailConfigurationRepository::class);

        $this->app->bind(DataFilterRepositoryInterface::class, DataFilterRepository::class);

        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderLineRepositoryInterface::class, OrderLineRepository::class);

        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CustomerVisitRepositoryInterface::class, CustomerVisitRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(ItemAttributeRepositoryInterface::class, ItemAttributeRepository::class);

        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(TranslationRepositoryInterface::class, TranslationRepository::class);

        $this->app->bind(CompanyLanguageRepositoryInterface::class, CompanyLanguageRepository::class);
        $this->app->bind(CompanyTranslationRepositoryInterface::class, CompanyTranslationRepository::class);

        $this->app->bind(WebShopLanguageRepositoryInterface::class, WebShopLanguageRepository::class);

    }
}
