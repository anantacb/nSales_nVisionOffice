<?php

namespace App\Http\Middleware;

use App\Models\Office\User;
use App\Transformer\ApiResponseTransformer;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserIsAdminOrDeveloper
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
        if (!$request->has('SelectedCompanyId')) {
            throw new Exception('Selected Company Id (SelectedCompanyId) is needed.', 422);
        }
        $user = Auth::user();
        $userCompanyWiseRoles = Cache::remember("UserCompanyWiseRoles-$user->Id", 60, function () use ($user) {
            $userWithRoles = User::with(['companyUsers.roles'])->where('Id', $user->Id)->first();
            $userCompanyWiseRoles = [];
            foreach ($userWithRoles->companyUsers as $companyUser) {
                $roleTypes = $companyUser->roles->pluck('Type')->toArray();
                $userCompanyWiseRoles[] = [
                    'CompanyId' => $companyUser->CompanyId,
                    'RoleTypes' => $roleTypes
                ];
            }
            return $userCompanyWiseRoles;
        });
        $selectedCompanyId = $request->input('SelectedCompanyId');
        $selectedCompany = collect($userCompanyWiseRoles)->firstWhere('CompanyId', $selectedCompanyId);
        if (!$selectedCompany || empty(array_intersect($selectedCompany['RoleTypes'], ['Developer', 'Administrator']))) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }
        return $next($request);
    }
}
