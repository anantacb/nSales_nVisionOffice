<?php

namespace App\Http\Middleware;

use App\Models\Office\User;
use App\Transformer\ApiResponseTransformer;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Gate a route by one or more role Types (OR semantics) — the request passes if the user holds ANY
 * of the listed Role.Type values in the selected company, e.g. `role:Manager,Employee`.
 *
 * Literal membership (no implicit Developer bypass): `role:Developer` is the old `developer`
 * middleware and `role:Developer,Administrator` is the old `admin-or-developer` middleware.
 */
class UserHasRole
{
    /**
     * @throws Exception
     */
    public function handle($request, Closure $next, string ...$roles)
    {
        if (empty($roles)) {
            throw new Exception('UserHasRole middleware requires at least one role.', 500);
        }

        if (!$request->has('SelectedCompanyId')) {
            throw new Exception('Selected Company Id (SelectedCompanyId) is needed.', 422);
        }

        $user = Auth::user();
        $userCompanyWiseRoles = Cache::remember("UserCompanyWiseRoles-$user->Id", 60, function () use ($user) {
            $userWithRoles = User::with(['companyUsers.roles'])->where('Id', $user->Id)->first();
            $userCompanyWiseRoles = [];
            foreach ($userWithRoles->companyUsers as $companyUser) {
                $userCompanyWiseRoles[] = [
                    'CompanyId' => $companyUser->CompanyId,
                    'RoleTypes' => $companyUser->roles->pluck('Type')->toArray()
                ];
            }
            return $userCompanyWiseRoles;
        });

        $selectedCompanyId = $request->input('SelectedCompanyId');
        $selectedCompany = collect($userCompanyWiseRoles)->firstWhere('CompanyId', $selectedCompanyId);
        if (!$selectedCompany || empty(array_intersect($selectedCompany['RoleTypes'], $roles))) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }

        return $next($request);
    }
}
