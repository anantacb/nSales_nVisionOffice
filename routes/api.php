<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmailConfigurationController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleSettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableFieldController;
use App\Http\Controllers\TableHelperController;
use App\Http\Controllers\UserController;
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
    Route::post('/table-details', [TableController::class, 'getDetails']);
    Route::post('/delete-table', [TableController::class, 'delete']);
    Route::post('/create-table-preview-sql', [TableController::class, 'getCreateTablePreview']);
    Route::post('/create-table-save-and-execute', [TableController::class, 'createTableSaveAndExecute']);
    Route::post('/create-table-save-without-executing', [TableController::class, 'createTableSaveWithoutExecuting']);

    // TableField
    Route::post('/table-fields', [TableFieldController::class, 'getTableFields']);
    Route::post('/table-fields-operation-sql-previews', [TableFieldController::class, 'tableFieldsOperationPreviews']);
    Route::post('/table-fields-operations-save-without-executing', [TableFieldController::class, 'tableFieldsOperationsSaveWithoutExecuting']);
    Route::post('/table-fields-operations-save-and-execute', [TableFieldController::class, 'tableFieldsOperationsSaveAndExecute']);


    // Module
    Route::post('/modules/all', [ModuleController::class, 'getAllModules']);
    Route::post('/modules/get-activated-and-available-modules-by-company', [ModuleController::class, 'getActivatedAndAvailableModulesByCompany']);
    Route::post('/modules/get-activated-modules-by-company', [ModuleController::class, 'getActivatedModulesByCompany']);
    Route::post('/modules/activate-module', [ModuleController::class, 'activateModule']);
    Route::post('/modules/deactivate-module', [ModuleController::class, 'deactivateModule']);
    Route::post('/modules/get-by-application', [ModuleController::class, 'getModulesByApplication']);

    // ModuleSetting
    Route::post('/module-setting/all-by-company', [ModuleSettingController::class, 'getAllModuleSettingsByCompany']);
    Route::post('/module-settings/update-by-company', [ModuleSettingController::class, 'updateModuleSettingsByCompany']);

    // Company
    Route::post('/companies/all', [CompanyController::class, 'getAllCompanies']);
    Route::post('/companies/by-module-enabled', [CompanyController::class, 'getModuleEnabledCompanies']);
    Route::post('/company/create', [CompanyController::class, 'create']);

    // Helpers
    Route::post('/table-helper/get-enum-values', [TableHelperController::class, 'getEnumValues']);

    // Role
    Route::post('/roles/by-company', [RoleController::class, 'getRolesByCompany']);

    // User
    Route::post('/users/company-user/create', [UserController::class, 'createCompanyUser']);
    Route::post('/users/get-company-users', [UserController::class, 'getCompanyUsers']);

    // Application
    Route::post('applications/all', [ApplicationController::class, 'getAllApplications']);

    // EmailConfiguration
    Route::post('email-configuration/create', [EmailConfigurationController::class, 'create']);
});
