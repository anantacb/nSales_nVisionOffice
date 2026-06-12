<?php

namespace Database\Seeders;

use App\Models\Office\Permission;
use App\Models\Office\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RolePermissionBackfillSeeder extends Seeder
{
    public function run(): void
    {
        // Filter on Aliases — legacy pre-migration rows without an Aliases slug are not part of
        // the new permission system and must not be granted to bypass roles.
        $catalog = Permission::whereNotNull('Aliases')->where('Aliases', '<>', '');
        $developerPermissionIds = (clone $catalog)->pluck('Id')->all();
        $administratorPermissionIds = (clone $catalog)->where('IsDeveloperOnly', 0)->pluck('Id')->all();
        $developerOnlyPermissionIds = (clone $catalog)->where('IsDeveloperOnly', 1)->pluck('Id')->all();

        if (empty($developerPermissionIds)) {
            $this->command?->warn('RolePermissionBackfillSeeder: Permission catalog is empty. Run PermissionSeeder first.');
            return;
        }

        $roles = Role::whereIn('Type', ['Developer', 'Administrator'])->get(['Id', 'Type']);

        $now = Carbon::now();
        $rows = [];
        foreach ($roles as $role) {
            $permissionIds = $role->Type === 'Developer'
                ? $developerPermissionIds
                : $administratorPermissionIds;

            foreach ($permissionIds as $permissionId) {
                $rows[] = [
                    'RoleId'       => $role->Id,
                    'PermissionId' => $permissionId,
                    'InsertTime'   => $now,
                    'UpdateTime'   => $now,
                ];
            }
        }

        // UNIQUE(RoleId, PermissionId) on RolePermission makes this idempotent —
        // re-runs leave existing grants untouched.
        $inserted = 0;
        foreach (array_chunk($rows, 500) as $chunk) {
            $inserted += DB::table('RolePermission')->insertOrIgnore($chunk);
        }

        // Strip stale grants: a non-Developer role must never retain a developer-only permission
        // (e.g. an Administrator or custom role that held a permission before it was re-flagged as
        // core/developer-only). The middleware already blocks these, but the data is reconciled here.
        // Targets ALL non-Developer roles, not just bypass Types, since custom roles can hold grants
        // from the EditRole UI or a prior DefaultRolePermissionSeeder run. Idempotent.
        $strippedGrants = 0;
        if (!empty($developerOnlyPermissionIds)) {
            $nonDeveloperRoleIds = Role::where('Type', '<>', 'Developer')->pluck('Id')->all();
            if (!empty($nonDeveloperRoleIds)) {
                $strippedGrants = DB::table('RolePermission')
                    ->whereIn('PermissionId', $developerOnlyPermissionIds)
                    ->whereIn('RoleId', $nonDeveloperRoleIds)
                    ->delete();
            }
        }

        $this->command?->info(
            "RolePermissionBackfillSeeder: processed {$roles->count()} bypass-type role(s), "
            . "inserted {$inserted} new grant(s), stripped {$strippedGrants} stale developer-only grant(s)."
        );
    }
}
