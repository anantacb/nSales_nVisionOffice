<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableFieldController;
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
    // Tables
    Route::post('/tables', [TableController::class, 'getTables']);
    Route::post('/table-details', [TableController::class, 'getDetails']);
    Route::post('/delete-table', [TableController::class, 'delete']);
    Route::post('/create-table-preview-sql', [TableController::class, 'getCreateTablePreview']);
    Route::post('/create-table-save-and-execute', [TableController::class, 'createTableSaveAndExecute']);
    Route::post('/create-table-save-without-executing', [TableController::class, 'createTableSaveWithoutExecuting']);

    // TableFields
    Route::post('/table-fields', [TableFieldController::class, 'getTableFields']);
    Route::post('/table-fields-operation-sql-previews', [TableFieldController::class, 'tableFieldsOperationPreviews']);
    Route::post('/table-fields-operations-save-without-executing', [TableFieldController::class, 'tableFieldsOperationsSaveWithoutExecuting']);
    Route::post('/table-fields-operations-save-and-execute', [TableFieldController::class, 'tableFieldsOperationsSaveAndExecute']);


    // Modules
    Route::post('/modules/all', [ModuleController::class, 'getAllModules']);

    // Companies
    Route::post('/companies/all', [CompanyController::class, 'getAllCompanies']);
    Route::post('/companies/by-module-enabled', [CompanyController::class, 'getModuleEnabledCompanies']);
});
