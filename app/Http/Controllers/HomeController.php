<?php

namespace App\Http\Controllers;

use App\Repositories\Plugin\B2bGqlApi\B2bGqlApiRepository;
use App\Repositories\Plugin\NsalesOfficeRestApi\NsalesOfficeRestApiRepository;
use App\Repositories\Plugin\NvmGqlApi\NvmGqlApiRepository;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    protected B2bGqlApiRepository $b2bGqlApiRepository;

    protected NvmGqlApiRepository $nvmGqlApiRepository;

    protected NsalesOfficeRestApiRepository $nsalesOfficeRestApiRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        B2bGqlApiRepository           $b2bGqlApiRepository,
        NvmGqlApiRepository           $nvmGqlApiRepository,
        NsalesOfficeRestApiRepository $nsalesOfficeRestApiRepository
    )
    {
        $this->middleware('auth');
        $this->b2bGqlApiRepository = $b2bGqlApiRepository;
        $this->nvmGqlApiRepository = $nvmGqlApiRepository;
        $this->nsalesOfficeRestApiRepository = $nsalesOfficeRestApiRepository;
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

    public function cacheClear(Request $request)
    {
        if ($request->get('nvisionOfficeCache')) {
            Artisan::call('cache:clear');
        }
        if ($request->get('nsalesOfficeCache')) {
            $this->nsalesOfficeRestApiRepository->cacheClear();
        }
        if ($request->get('nvmGqlCache')) {
            $this->nvmGqlApiRepository->cacheClear();
        }
        if ($request->get('b2bGqlCache')) {
            $this->b2bGqlApiRepository->cacheClear();
        }
        return ApiResponseTransformer::success([], 'Cache Cleared Successfully.');
    }
}
