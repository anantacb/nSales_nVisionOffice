<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationModuleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\B2bGqlApiController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyLanguageController;
use App\Http\Controllers\CompanyTranslationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerVisitController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DataFilterController;
use App\Http\Controllers\DocumentAPIController;
use App\Http\Controllers\EmailConfigurationController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModulePackageController;
use App\Http\Controllers\ModulePackageModuleController;
use App\Http\Controllers\ModuleSettingController;
use App\Http\Controllers\OrderByCustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableFieldController;
use App\Http\Controllers\TableHelperController;
use App\Http\Controllers\TableIndexController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebShopPageController;
use App\Http\Controllers\WebShopUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
});

Route::prefix('auth')->middleware(['auth:api'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('user', [UserController::class, 'authUserDetails']);
});

Route::middleware(['auth:api'])->group(function () {
    // Table
    Route::post('/tables', [TableController::class, 'getTables']);
    Route::post('/table/details', [TableController::class, 'getDetails']);
    Route::post('/table/details-by-name', [TableController::class, 'getDetailsByName']);
    Route::post('/table/delete', [TableController::class, 'delete']);
    Route::post('/table/update', [TableController::class, 'update']);
    Route::post('/table/get-by-module', [TableController::class, 'getByModule']);
    Route::post('/create-table-preview-sql', [TableController::class, 'getCreateTablePreview']);
    Route::post('/create-table-save-and-execute', [TableController::class, 'createTableSaveAndExecute']);
    Route::post('/create-table-save-without-executing', [TableController::class, 'createTableSaveWithoutExecuting']);

    // TableField
    Route::post('/table-fields', [TableFieldController::class, 'getTableFields']);
    Route::post('/table-fields-operation-sql-previews', [TableFieldController::class, 'tableFieldsOperationPreviews']);
    Route::post('/table-fields-operations-save-without-executing', [TableFieldController::class, 'tableFieldsOperationsSaveWithoutExecuting']);
    Route::post('/table-fields-operations-save-and-execute', [TableFieldController::class, 'tableFieldsOperationsSaveAndExecute']);

    Route::post('/general-table-fields', [TableFieldController::class, 'getGeneralTableFields']);
    Route::post('/company-specific-table-fields', [TableFieldController::class, 'getCompanySpecificTableFields']);
    Route::post('/company-all-table-fields', [TableFieldController::class, 'getCompanyAllTableFields']);

    // TableIndex
    Route::post('/table-indices', [TableIndexController::class, 'getTableIndices']);
    Route::post('/table-indices-operation-sql-previews', [TableIndexController::class, 'tableIndicesOperationPreviews']);
    Route::post('/table-indices-operations-save-without-executing', [TableIndexController::class, 'tableIndicesOperationsSaveWithoutExecuting']);
    Route::post('/table-indices-operations-save-and-execute', [TableIndexController::class, 'tableIndicesOperationsSaveAndExecute']);

    // Module
    Route::post('/modules', [ModuleController::class, 'getModules']);
    Route::post('/module/create', [ModuleController::class, 'create']);
    Route::post('/module/update', [ModuleController::class, 'update']);
    Route::post('/module/details', [ModuleController::class, 'details']);
    Route::post('/module/delete', [ModuleController::class, 'delete']);
    Route::post('/module/all', [ModuleController::class, 'getAllModules']);
    Route::post('/module/get-activated-and-available-modules-by-company', [ModuleController::class, 'getActivatedAndAvailableModulesByCompany']);
    Route::post('/module/get-activated-modules-by-company', [ModuleController::class, 'getActivatedModulesByCompany']);
    Route::post('/module/activate-module', [ModuleController::class, 'activateModule']);
    Route::post('/module/deactivate-module', [ModuleController::class, 'deactivateModule']);
    Route::post('/module/get-by-application', [ModuleController::class, 'getModulesByApplication']);
    Route::post('/module/get-assignable-modules-by-application', [ModuleController::class, 'getAssignableModulesByApplication']);
    Route::post('/module/get-assignable-modules-by-module-package', [ModuleController::class, 'getAssignableModulesByModulePackage']);

    // ApplicationModule
    Route::post('/application-module/create', [ApplicationModuleController::class, 'create']);
    Route::post('/application-module/delete', [ApplicationModuleController::class, 'delete']);
    Route::post('/application-module/update', [ApplicationModuleController::class, 'update']);

    // ModulePackageModule
    Route::post('/module-package-module/delete', [ModulePackageModuleController::class, 'delete']);
    Route::post('/module-package-module/create', [ModulePackageModuleController::class, 'create']);

    // ModuleSetting
    Route::post('/module-settings', [ModuleSettingController::class, 'getModuleSettings']);
    Route::post('/module-setting/create', [ModuleSettingController::class, 'create']);
    Route::post('/module-setting/update', [ModuleSettingController::class, 'update']);
    Route::post('/module-setting/delete', [ModuleSettingController::class, 'delete']);
    Route::post('/module-setting/details', [ModuleSettingController::class, 'details']);
    Route::post('/module-setting/all-by-company', [ModuleSettingController::class, 'getAllModuleSettingsByCompany']);
    Route::post('/module-setting/update-by-company', [ModuleSettingController::class, 'updateModuleSettingsByCompany']);
    Route::post('/module-setting/by-name', [ModuleSettingController::class, 'getModuleSettingsByName']);

    Route::post('/get-all-companies-with-db', [DatabaseController::class, 'getAllCompanies']);
    Route::post('/copy-db-to-dev', [DatabaseController::class, 'copyDBtoDev']);

    // Company
    Route::post('/companies', [CompanyController::class, 'getCompanies']);
    Route::post('/company/create', [CompanyController::class, 'create']);
    Route::post('/company/update', [CompanyController::class, 'update']);
    Route::post('/company/details', [CompanyController::class, 'details']);
    Route::post('/company/delete', [CompanyController::class, 'delete']);

    Route::post('/company/all', [CompanyController::class, 'getAllCompanies']);
    Route::post('/auth-user-companies', [CompanyController::class, 'getAuthUserCompanies']);

    Route::post('/company/by-module-enabled', [CompanyController::class, 'getModuleEnabledCompanies']);
    Route::post('/company/assignable-companies-by-user', [CompanyController::class, 'getAssignableCompaniesByUser']);


    // Helpers
    Route::post('/table-helper/get-enum-values', [TableHelperController::class, 'getEnumValues']);
    Route::post('/table-helper/get-column-distinct-values', [TableHelperController::class, 'getColumnDistinctValues']);

    // Role
    Route::post('/roles/by-company', [RoleController::class, 'getRolesByCompany']);

    // User
    Route::post('/users', [UserController::class, 'getUsers']);
    Route::post('/user/details', [UserController::class, 'details']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/user/delete', [UserController::class, 'delete']);
    Route::post('/user/assign-to-company', [UserController::class, 'assignToCompany']);

    Route::post('/users/developers', [UserController::class, 'getDevelopers']);
    Route::post('/users/developer/tag-developer-to-all-companies', [UserController::class, 'tagDeveloperToAllCompanies']);

    Route::post('/users/company-users', [UserController::class, 'getCompanyUsers']);
    Route::post('/users/company-user/create', [UserController::class, 'createCompanyUser']);
    Route::post('/users/company-user/update', [UserController::class, 'updateCompanyUser']);
    Route::post('/users/company-user/delete', [UserController::class, 'deleteCompanyUser']);
    Route::post('/users/company-user/details', [UserController::class, 'companyUserDetails']);

    Route::post('/users/get-all-company-users', [UserController::class, 'getAllCompanyUsers']);

    // Application
    Route::post('/applications/all', [ApplicationController::class, 'getAllApplications']);
    Route::post('/applications', [ApplicationController::class, 'getApplications']);
    Route::post('/application/create', [ApplicationController::class, 'create']);
    Route::post('/application/update', [ApplicationController::class, 'update']);
    Route::post('/application/delete', [ApplicationController::class, 'delete']);
    Route::post('/application/details', [ApplicationController::class, 'details']);

    // ModulePackage
    Route::post('/module-packages/all', [ModulePackageController::class, 'getAllModulePackages']);
    Route::post('/module-packages', [ModulePackageController::class, 'getModulePackages']);
    Route::post('/module-package/create', [ModulePackageController::class, 'create']);
    Route::post('/module-package/update', [ModulePackageController::class, 'update']);
    Route::post('/module-package/delete', [ModulePackageController::class, 'delete']);
    Route::post('/module-package/details', [ModulePackageController::class, 'details']);

    // Language
    Route::post('/languages/all', [LanguageController::class, 'getAllLanguages']);
    Route::post('/languages', [LanguageController::class, 'getLanguages']);
    Route::post('/language/create', [LanguageController::class, 'create']);
    Route::post('/language/update', [LanguageController::class, 'update']);
    Route::post('/language/delete', [LanguageController::class, 'delete']);
    Route::post('/language/details', [LanguageController::class, 'details']);

    // Translation
    Route::post('/translations', [TranslationController::class, 'getTranslations']);
    Route::post('/translation/create', [TranslationController::class, 'create']);
    Route::post('/translation/update', [TranslationController::class, 'update']);
    Route::post('/translation/delete', [TranslationController::class, 'delete']);
    Route::post('/translation/details', [TranslationController::class, 'details']);
    Route::post('/translations/sync', [TranslationController::class, 'sync']);

    // EmailConfiguration
    Route::post('/email-configurations', [EmailConfigurationController::class, 'getEmailConfigurations']);
    Route::post('/email-configurations/company-email-configurations', [EmailConfigurationController::class, 'getCompanyEmailConfigurations']);
    Route::post('/email-configuration/create', [EmailConfigurationController::class, 'create']);
    Route::post('/email-configuration/update', [EmailConfigurationController::class, 'update']);
    Route::post('/email-configuration/delete', [EmailConfigurationController::class, 'delete']);
    Route::post('/email-configuration/details', [EmailConfigurationController::class, 'details']);

    // DataFilter
    Route::post('/data-filters', [DataFilterController::class, 'getDataFilters']);
    Route::post('/data-filter/create', [DataFilterController::class, 'create']);
    Route::post('/data-filter/update', [DataFilterController::class, 'update']);
    Route::post('/data-filter/details', [DataFilterController::class, 'details']);
    Route::post('/data-filter/delete', [DataFilterController::class, 'delete']);

    Route::post('/data-filters/company-data-filters', [DataFilterController::class, 'getCompanyDataFilters']);
    Route::post('/data-filters/get-filter-result', [DataFilterController::class, 'getFilterResult']);

    // Role
    Route::post('/roles/company-roles', [RoleController::class, 'getCompanyRoles']);
    Route::post('/role/create', [RoleController::class, 'create']);
    Route::post('/role/update', [RoleController::class, 'update']);
    Route::post('/role/delete', [RoleController::class, 'delete']);
    Route::post('/role/details', [RoleController::class, 'details']);

    // Company theme
    Route::post('/company-theme', [\App\Http\Controllers\ThemeController::class, 'getCompanyTheme']);

    Route::middleware(['company'])->group(function () {
        // Order
        Route::post('/orders', [OrderController::class, 'getOrders']);
        Route::post('/open-orders', [OrderController::class, 'getOpenOrders']);
        Route::post('/failed-orders', [OrderController::class, 'getFailedOrders']);
        Route::post('/order/details', [OrderController::class, 'details']);
        Route::post('/order/delete', [OrderController::class, 'delete']);
        Route::post('/order/origins-get', [OrderController::class, 'getOrderOriginOptions']);
        Route::post('/order/re-export', [OrderController::class, 'reExportOrder']);

        // Order By Customer
        Route::post('/customer/latest/orders', [OrderByCustomerController::class, 'latestOrdersByCustomer']);

        // Customer
        Route::post('/customers', [CustomerController::class, 'getCustomers']);
        Route::post('/customer/create', [CustomerController::class, 'create']);
        Route::post('/customer/update', [CustomerController::class, 'update']);
        Route::post('/customer/delete', [CustomerController::class, 'delete']);
        Route::post('/customer/details', [CustomerController::class, 'details']);

        // Customer Visits
        Route::post('/customer-visits', [CustomerVisitController::class, 'getCustomerVisits']);
        Route::post('/customer-visits/get-distinct-value', [CustomerVisitController::class, 'getDistinctValue']);

        // Company Language
        Route::post('/company-languages/all', [CompanyLanguageController::class, 'getAllCompanyLanguages']);
        Route::post('/company-languages', [CompanyLanguageController::class, 'getCompanyLanguages']);
        Route::post('/company-language/delete', [CompanyLanguageController::class, 'delete']);
        Route::post('/company-language/set-as-default-language', [CompanyLanguageController::class, 'setAsDefaultLanguage']);
        Route::post('/company-language/add-company-language', [CompanyLanguageController::class, 'addCompanyLanguage']);

        // Company Translation
        Route::post('/company-translations', [CompanyTranslationController::class, 'getCompanyTranslations']);
        Route::post('/company-translation/create', [CompanyTranslationController::class, 'create']);
        Route::post('/company-translation/update', [CompanyTranslationController::class, 'update']);
        Route::post('/company-translation/delete', [CompanyTranslationController::class, 'delete']);
        Route::post('/company-translation/details', [CompanyTranslationController::class, 'details']);

        // WebShopUser
        Route::post('/web-shop-user/details', [WebShopUserController::class, 'details']);
        Route::post('/web-shop-user/create-test-user', [WebShopUserController::class, 'createTestUser']);

        // WebShopPage
        Route::post('/web-shop-page/list', [WebShopPageController::class, 'list']);
        Route::post('/web-shop-page/create-pages', [WebShopPageController::class, 'createPages']);
        Route::post('/web-shop-page/create-pages-content-for-missing-languages', [WebShopPageController::class, 'createPagesContentForMissingLanguages']);

        // B2bGqlApi
        Route::post('/b2b-gql-api/get-itemgroups-item', [B2bGqlApiController::class, 'getItemGroupsAndItem']);

        // Document api
        Route::post('/company-document-api', [DocumentAPIController::class, 'getCompanyDocumentApi']);

        // Git api
        Route::post('/company-git-branches', [GitController::class, 'getCompanyBranches']);
        Route::post('/company-git-branches/create', [GitController::class, 'createCompanyBranches']);
    });
});
