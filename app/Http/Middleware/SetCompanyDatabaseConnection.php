<?php

namespace App\Http\Middleware;

use App\Services\Company\CompanyService;
use Closure;
use Exception;
use Illuminate\Http\Request;

class SetCompanyDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        if (!$request->has('CompanyId')) {
            throw new Exception('Company Id (CompanyId) is needed.', 422);
        }

        CompanyService::setCompanyDatabaseConnection($request->get('CompanyId'));
        return $next($request);
    }
}
