<?php

namespace App\Services\DefaultRole;

use App\Models\Office\Permission;
use App\Models\Office\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Projects DefaultRole template permissions onto company roles.
 *
 * A "DefaultRole template" is a Role row with CompanyId = NULL — one per Type. Each company role
 * (CompanyId NOT NULL) is matched back to its template purely by Role.Type and granted the template's
 * permissions. Projection is additive (insertOrIgnore against the UNIQUE(RoleId, PermissionId) index):
 * it only adds missing grants and never removes existing ones, so EditRole customizations are kept.
 *
 * Single source of truth shared by the permissions:* console commands (via the
 * ProjectsDefaultRolePermissions trait) and the GUI sync endpoints (via DefaultRoleService).
 */
class DefaultRolePermissionProjector
{
    /**
     * @param string[]|null $types           Limit to these Role.Types (null = every template Type).
     * @param int[]|null    $companyIds      Limit to company roles in these companies (null = all).
     * @param string[]|null $permissionSlugs Limit to these Permission.Aliases (null = the template's full set).
     * @param bool          $dryRun          Compute the delta without writing.
     *
     * @return array{rolesProcessed:int, grantsInserted:int, skippedTypes:string[], unknownSlugs:string[]}
     */
    public function project(
        ?array $types,
        ?array $companyIds,
        ?array $permissionSlugs,
        bool   $dryRun
    ): array {
        // Templates keyed by Type.
        $templates = Role::whereNull('CompanyId')->get(['Id', 'Type'])->keyBy('Type');

        // Resolve the optional slug filter to a set of allowed PermissionIds.
        $allowedPermissionIds = null;
        $unknownSlugs = [];
        if (!empty($permissionSlugs)) {
            $found = Permission::whereIn('Aliases', $permissionSlugs)->pluck('Id', 'Aliases');
            foreach ($permissionSlugs as $slug) {
                if (!$found->has($slug)) {
                    $unknownSlugs[] = $slug;
                }
            }
            $allowedPermissionIds = array_flip($found->values()->all());
        }

        $typesToProcess = !empty($types) ? $types : $templates->keys()->all();

        $now = Carbon::now();
        $rolesProcessed = 0;
        $grantsInserted = 0;
        $skippedTypes = [];

        foreach ($typesToProcess as $type) {
            $template = $templates->get($type);
            if (!$template) {
                $skippedTypes[] = $type;
                continue;
            }

            $templatePermissionIds = DB::table('RolePermission')
                ->where('RoleId', $template->Id)
                ->whereNull('DeleteTime')
                ->pluck('PermissionId')
                ->all();

            if ($allowedPermissionIds !== null) {
                $templatePermissionIds = array_values(array_filter(
                    $templatePermissionIds,
                    fn ($id) => isset($allowedPermissionIds[$id])
                ));
            }

            if (empty($templatePermissionIds)) {
                continue;
            }

            $companyRolesQuery = Role::whereNotNull('CompanyId')->where('Type', $type);
            if (!empty($companyIds)) {
                $companyRolesQuery->whereIn('CompanyId', $companyIds);
            }
            $companyRoleIds = $companyRolesQuery->pluck('Id')->all();

            foreach ($companyRoleIds as $companyRoleId) {
                $rolesProcessed++;

                $existing = DB::table('RolePermission')
                    ->where('RoleId', $companyRoleId)
                    ->whereNull('DeleteTime')
                    ->pluck('PermissionId')
                    ->all();

                $missing = array_values(array_diff($templatePermissionIds, $existing));
                if (empty($missing)) {
                    continue;
                }

                $grantsInserted += count($missing);

                if (!$dryRun) {
                    $rows = array_map(fn ($pid) => [
                        'RoleId' => $companyRoleId,
                        'PermissionId' => $pid,
                        'InsertTime' => $now,
                        'UpdateTime' => $now,
                    ], $missing);

                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table('RolePermission')->insertOrIgnore($chunk);
                    }
                }
            }
        }

        return compact('rolesProcessed', 'grantsInserted', 'skippedTypes', 'unknownSlugs');
    }
}
