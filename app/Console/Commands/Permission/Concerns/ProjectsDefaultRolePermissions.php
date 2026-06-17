<?php

namespace App\Console\Commands\Permission\Concerns;

use App\Services\DefaultRole\DefaultRolePermissionProjector;

/**
 * Shared projection helpers for the permissions:* commands.
 *
 * The projection itself lives in {@see DefaultRolePermissionProjector} (the single source of truth
 * shared with the GUI sync endpoints); this trait only adapts it to the command option signature and
 * prints the report.
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
        return app(DefaultRolePermissionProjector::class)
            ->project($types, $companyIds, $permissionSlugs, $dryRun);
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
