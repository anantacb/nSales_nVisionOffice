<?php

namespace Database\Seeders;

use App\Services\DefaultRole\DefaultRolePermissionProjector;
use Illuminate\Database\Seeder;

class RolePermissionBackfillSeeder extends Seeder
{
    /**
     * Backfill every existing company role from its matching default template: for each Role.Type,
     * copy the CompanyId = NULL template's permission grants onto all company roles (CompanyId NOT NULL)
     * of the same Type. Additive (insertOrIgnore against UNIQUE(RoleId, PermissionId)) — existing grants
     * and EditRole customizations are preserved. Runs after DefaultRolePermissionSeeder, which grants the
     * templates this reads from.
     */
    public function run(): void
    {
        $report = (new DefaultRolePermissionProjector())->project(null, null, null, false);

        $this->command?->info(
            "RolePermissionBackfillSeeder: processed {$report['rolesProcessed']} company role(s), "
            . "inserted {$report['grantsInserted']} new grant(s)."
        );

        if (!empty($report['skippedTypes'])) {
            $this->command?->warn(
                'RolePermissionBackfillSeeder: no default template for Type(s): '
                . implode(', ', array_unique($report['skippedTypes'])) . '.'
            );
        }
    }
}
