<?php

use App\Repositories\Plugin\BunnyCdn\BunnyCdnRepository;
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

function generate_alternative_name($base_name, $attempt)
{
    if ($attempt !== 1) {
        // Generate a random suffix to append to the base name
        $suffix = substr(md5(uniqid(rand(), true)), 0, 3);
        return "{$base_name}{$attempt}{$suffix}";
    }
    return $base_name;
}

Route::get('/test', function () {
    $repo = new BunnyCdnRepository();
    $baseName = "b2bmaster";
    $attempt = 0;
    $max_attempts = 3;

    while ($attempt < $max_attempts) {
        $attempt++;
        $name = generate_alternative_name($baseName, $attempt);
        $addStorage = $repo->addStorageZone($name);
        if ($addStorage["code"] == 400 && isset($addStorage['message']) && str_contains(strtolower($addStorage['message']), 'name is already taken')) {
            sleep(1);  // Optional: wait before retrying
        } else {
            $addPullZone = $repo->addPullZone($name, $addStorage['data']['Id']);
            $attempt = $max_attempts;
        }
    }


    /*$addStorage = BunnyCdnService::addStorageZone("swadhin10");
    $addPullZone = BunnyCdnService::addPullZone("swadhin10");*/
    dd($addStorage, $addPullZone);
});

/*Route::get('/', function () {
    return view('app');
});*/

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');




//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
