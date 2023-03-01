<?php

use App\Models\Office\Module;
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

Route::get('/test', function () {

    $module = Module::with(['applications'])->where('Id', 5)->first();
    dd($module->toArray());
    dd(date_default_timezone_get());
});

/*Route::get('/', function () {
    return view('app');
});*/

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');




//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
