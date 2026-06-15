<?php

namespace App\Console\Commands\Permission;

use App\Console\Commands\Permission\Concerns\ProjectsDefaultRolePermissions;
use Illuminate\Console\Command;

/**
 * One-off backfill: grant every company role its DefaultRole template's full permission baseline.
 * Additive and idempotent — safe to re-run. Use the top-up command for targeted propagation.
 */
class BackfillRolePermissionsFromDefaults extends Command
{
    use ProjectsDefaultRolePermissions;

    protected $signature = 'permissions:backfill-from-default-roles
                            {--companyId=* : Limit to these company id(s); default = all companies}
                            {--dry-run : Report the delta without writing}';

    protected $description = 'Backfill every company role with its DefaultRole template permission baseline (additive).';

    public function handle(): int
    {
        $companyIds = $this->option('companyId') ?: null;
        $dryRun = (bool) $this->option('dry-run');

        $report = $this->projectDefaultRolePermissions(null, $companyIds, null, $dryRun);
        $this->printProjectionReport($report, $dryRun);

        return self::SUCCESS;
    }
}
