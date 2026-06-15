<?php

namespace App\Console\Commands\Permission\Concerns;

use App\Models\Office\Permission;
use App\Models\Office\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shared projection logic for the permissions:* commands.
 *
 * A "DefaultRole template" is a Role row with CompanyId = NULL — one per Type. Each company role
 * (CompanyId NOT NULL) is matched back to its template purely by Role.Type and granted the template's
 * permissions. Projection is additive (insertOrIgnore against the UNIQUE(RoleId, PermissionId) index):
 * it only adds missing grants and never removes existing ones, so EditRole customizations are kept.
 */
trait ProjectsDefaultRolePermissions
{
    /**
     * @param string[]|null $types          Limit to these Role.Types (null = every template Type).
     * @param int[]|null    $companyIds     Limit to company roles in these companies (null = all).
     * @param string[]|null $permissionSlugs Limit to these Permission.Aliases (null = the template's full set).
     * @param bool          $dryRun         Compute the delta without writing.
     *
     * @return array{rolesProcessed:int, grantsInserted:int, skippedTypes:string[], unknownSlugs:string[]}
     */
    protected function projectDefaultRolePermissions(
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

    /**
     * Print the projection report.
     *
     * @param array{rolesProcessed:int, grantsInserted:int, skippedTypes:string[], unknownSlugs:string[]} $report
     */
    protected function printProjectionReport(array $report, bool $dryRun): void
    {
        $verb = $dryRun ? 'would grant' : 'granted';
        $this->info(sprintf(
            '%sprocessed %d company role(s), %s %d new permission grant(s).',
            $dryRun ? '[dry-run] ' : '',
            $report['rolesProcessed'],
            $verb,
            $report['grantsInserted']
        ));

        if (!empty($report['skippedTypes'])) {
            $this->warn('No DefaultRole template for Type(s): '
                . implode(', ', array_unique($report['skippedTypes'])) . '.');
        }
        if (!empty($report['unknownSlugs'])) {
            $this->warn('No Permission row for slug(s): '
                . implode(', ', array_unique($report['unknownSlugs'])) . '.');
        }
    }
}
