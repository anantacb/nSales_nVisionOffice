<?php

namespace App\Http\Middleware;

use App\Models\Office\Permission;
use App\Models\Office\User;
use App\Transformer\ApiResponseTransformer;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Gate a route by a permission slug (Permission.Aliases), e.g. `permission:Order.Read`.
 *
 * Bypass rules (per docs/role-permission-implementation.md):
 *  - Developer role-Type passes every gate, including developer-only permissions.
 *  - Administrator role-Type passes every gate EXCEPT permissions flagged IsDeveloperOnly = 1.
 *  - Every other role must hold the explicit grant.
 *
 * Scoped per-company via the request's SelectedCompanyId. Complements the `role` middleware
 * (which gates on Role.Type); this one gates on a specific permission slug.
 */
class UserHasPermission
{
    /**
     * One or more permission slugs may be given (OR semantics) — the user passes if they satisfy
     * ANY of them, e.g. `permission:Order.Read,Order.Update`.
     *
     * @throws Exception
     */
    public function handle($request, Closure $next, string ...$permissions)
    {
        if (empty($permissions)) {
            throw new Exception('UserHasPermission middleware requires at least one permission slug.', 500);
        }

        if (!$request->has('SelectedCompanyId')) {
            throw new Exception('Selected Company Id (SelectedCompanyId) is needed.', 422);
        }

        $user = Auth::user();
        $selectedCompanyId = (int) $request->input('SelectedCompanyId');

        $access = Cache::remember("UserCompanyWiseAccess-$user->Id", 60, function () use ($user) {
            $userWithAccess = User::with(['companyUsers.roles.permissions:Id,Aliases'])
                ->where('Id', $user->Id)
                ->first();

            $access = [];
            foreach ($userWithAccess->companyUsers as $companyUser) {
                $access[] = [
                    'CompanyId' => $companyUser->CompanyId,
                    'RoleTypes' => $companyUser->roles->pluck('Type')->all(),
                    'Permissions' => $companyUser->roles
                        ->pluck('permissions')
                        ->flatten()
                        ->pluck('Aliases')
                        ->unique()
                        ->values()
                        ->all(),
                ];
            }
            return $access;
        });

        $entry = collect($access)->firstWhere('CompanyId', $selectedCompanyId);
        if (!$entry) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }

        // Developer passes everything, including developer-only permissions.
        if (in_array('Developer', $entry['RoleTypes'], true)) {
            return $next($request);
        }

        $developerOnly = Cache::remember('PermissionCatalog-DeveloperOnly', 3600, function () {
            return Permission::where('IsDeveloperOnly', 1)->pluck('Aliases')->all();
        });

        // Developer-only permissions never grant a non-Developer access — drop them from the
        // candidate set before applying the Administrator bypass / explicit-grant checks.
        $grantable = array_values(array_filter(
            $permissions,
            fn ($permission) => !in_array($permission, $developerOnly, true)
        ));

        // The user passes if they satisfy ANY remaining (non-developer-only) slug.
        $isAdministrator = in_array('Administrator', $entry['RoleTypes'], true);
        foreach ($grantable as $permission) {
            if ($isAdministrator || in_array($permission, $entry['Permissions'], true)) {
                return $next($request);
            }
        }

        return ApiResponseTransformer::error([], 'Access Denied.', 403);
    }
}
