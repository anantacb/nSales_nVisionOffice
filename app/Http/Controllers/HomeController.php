<?php

namespace App\Http\Controllers;

use App\Transformer\ApiResponseTransformer;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function cacheClear()
    {
        Artisan::call('cache:clear');
        return ApiResponseTransformer::success([], 'Cache Cleared.');
    }
}
