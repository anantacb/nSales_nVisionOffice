<?php

use App\Models\Office\Table;
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

Route::get('/', function () {
    return view('app');
});

Route::get('/test', function () {
    /*$table_fields = TableField::with('companyTableFields.company')
        //->whereHas('companyTableFields')
        ->where('TableId', 4)
        ->orderBy('SortOrder')->get();

    $table_fields->map(function () {

    });
    dd($table_fields[0]);*/

    $tables = Table::whereHas('companyTables')->with('companyTables.company')->get();
    dd($tables->toArray());
});

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
