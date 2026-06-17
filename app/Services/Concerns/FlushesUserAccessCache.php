<?php

namespace App\Services\Concerns;

use App\Models\Office\CompanyUserRole;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates the per-user access caches read by the auth/permission middleware so that role or
 * permission changes take effect immediately instead of after the 60s TTL.
 *
 * Two keys are cleared:
 *  - UserCompanyWiseAccess-{UserId} — role types + permission slugs (UserHasPermission middleware).
 *  - UserCompanyWiseRoles-{UserId}  — role types only (UserHasRole middleware).
 */
trait FlushesUserAccessCache
{
    protected function flushAccessCacheForUsers(iterable $userIds): void
    {
        foreach ($userIds as $userId) {
            Cache::forget("UserCompanyWiseAccess-$userId");
            Cache::forget("UserCompanyWiseRoles-$userId");
        }
    }

    /**
     * Flush the access cache for every user holding the given role (any company user assigned it).
     */
    protected function flushAccessCacheForRole(int $roleId): void
    {
        $userIds = CompanyUserRole::query()
            ->where('CompanyUserRole.RoleId', $roleId)
            ->join('CompanyUser', 'CompanyUser.Id', '=', 'CompanyUserRole.CompanyUserId')
            ->whereNull('CompanyUser.DeleteTime')
            ->pluck('CompanyUser.UserId')
            ->unique();

        $this->flushAccessCacheForUsers($userIds);
    }
}
