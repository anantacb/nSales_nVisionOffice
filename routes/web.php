<?php

use App\Repositories\Eloquent\Office\User\CompanyUserRepository;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function (CompanyUserRepository $companyUserRepository) {
    $developerUsersIds = $companyUserRepository->getByAttributes([], '', '', '', false,
        [
            [
                "relation" => "roles", "column" => "Type", "operator" => "=", "values" => "Developer"
            ]
        ]
    )->pluck('UserId')->unique()->values()->toArray();

    dd($developerUsersIds);
});

/*Route::get('/', function () {
    return view('app');
});*/

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');

//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
