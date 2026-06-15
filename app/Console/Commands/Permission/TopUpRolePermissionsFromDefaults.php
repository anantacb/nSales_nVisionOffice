<?php

namespace App\Console\Commands\Permission;

use App\Console\Commands\Permission\Concerns\ProjectsDefaultRolePermissions;
use Illuminate\Console\Command;

/**
 * Targeted top-up: after a DefaultRole template gains a permission, propagate it to all matching
 * company roles. Scope with --type and/or --permission; additive and idempotent.
 *
 * e.g. php artisan permissions:topup-from-default-roles --type=Employee --permission=Order.Read
 */
class TopUpRolePermissionsFromDefaults extends Command
{
    use ProjectsDefaultRolePermissions;

    protected $signature = 'permissions:topup-from-default-roles
                            {--type=* : Limit to these Role.Type(s); default = all template Types}
                            {--permission=* : Limit to these permission slug(s) (Aliases); default = the full template set}
                            {--companyId=* : Limit to these company id(s); default = all companies}
                            {--dry-run : Report the delta without writing}';

    protected $description = 'Propagate DefaultRole template permissions to matching company roles (additive); scope with --type/--permission.';

    public function handle(): int
    {
        $types = $this->option('type') ?: null;
        $slugs = $this->option('permission') ?: null;
        $companyIds = $this->option('companyId') ?: null;
        $dryRun = (bool) $this->option('dry-run');

        $report = $this->projectDefaultRolePermissions($types, $companyIds, $slugs, $dryRun);
        $this->printProjectionReport($report, $dryRun);

        return self::SUCCESS;
    }
}
