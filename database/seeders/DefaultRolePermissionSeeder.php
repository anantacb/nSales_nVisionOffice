<?php

namespace Database\Seeders;

use App\Models\Office\Permission;
use App\Models\Office\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DefaultRolePermissionSeeder extends Seeder
{
    // Seeds the DefaultRole templates — Role rows with CompanyId = NULL that are copied into every
    // newly created company — and grants each one a sensible default permission set so new companies
    // inherit a working permission baseline. Grants are additive (insertOrIgnore): a re-run
    //  never removes manual edits made through the EditRole UI.
    //
    // Developer / Administrator slug lists are computed dynamically (all / role-grantable) at run time.
    // The seven custom Types carry the curated slug lists below; everyone is a role-grantable
    // (IsDeveloperOnly = 0) permission — non-Developer templates must never hold a developer-only grant.
    private const array CRUD = ['Create', 'Read', 'Update', 'Delete'];

    private const array CATALOG = [
        'Developer' => [
            'name' => 'Developer',
            'description' => 'Developers have access to everything in the system. Strictly reserved for nSales Technical Staff.',
            // 'permissions' resolved dynamically: every Permission.
        ],
        'Administrator' => [
            'name' => 'Administrator',
            'description' => 'Administrators have access to everything in the Company Account.',
            // 'permissions' resolved dynamically: every role-grantable Permission (IsDeveloperOnly = 0).
        ],
        'Manager' => [
            'name' => 'Manager',
            'description' => 'Managers have access to all employee data in the Company Account.',
            'permissions' => [
                'Order.Create', 'Order.Read', 'Order.Update', 'Order.Delete',
                'OrderLine.Create', 'OrderLine.Read', 'OrderLine.Update', 'OrderLine.Delete',
                'Customer.Create', 'Customer.Read', 'Customer.Update', 'Customer.Delete',
                'Item.Create', 'Item.Read', 'Item.Update', 'Item.Delete',
                'ItemAttribute.Create', 'ItemAttribute.Read', 'ItemAttribute.Update', 'ItemAttribute.Delete',
                'DataFilter.Create', 'DataFilter.Read', 'DataFilter.Update', 'DataFilter.Delete',
                'Theme.Read', 'Theme.Update',
                'CompanyEmailTemplate.Create', 'CompanyEmailTemplate.Read', 'CompanyEmailTemplate.Update', 'CompanyEmailTemplate.Delete',
                'CompanyEmailLayout.Read',
            ],
        ],
        'Employee' => [
            'name' => 'Employee',
            'description' => 'Employees have access to their own data in the Company Account.',
            'permissions' => [
                'Order.Create', 'Order.Read', 'Order.Update',
                'OrderLine.Create', 'OrderLine.Read', 'OrderLine.Update',
                'Customer.Read', 'Customer.Update',
                'Item.Read',
                'ItemAttribute.Read',
            ],
        ],
        'Client' => [
            'name' => 'Client',
            'description' => 'Clients have restricted access to the Company Account, scoped to the linked Customer.',
            'permissions' => [
                'Order.Read',
                'OrderLine.Read',
                'Item.Read',
                'ItemAttribute.Read',
            ],
        ],
        'Retailer' => [
            'name' => 'Retailer',
            'description' => 'Role of Retailer.',
            'permissions' => [
                'Order.Create', 'Order.Read', 'Order.Update',
                'OrderLine.Create', 'OrderLine.Read', 'OrderLine.Update',
                'Customer.Read',
                'Item.Read',
                'ItemAttribute.Read',
            ],
        ],
        'WebShopViewer' => [
            'name' => 'WebShopViewer',
            'description' => 'Role of Webshop viewer user.',
            'permissions' => [
                'WebShopText.Read',
                'WebShopPage.Read',
                'Item.Read',
                'ItemAttribute.Read',
            ],
        ],
        'Insights' => [
            'name' => 'Insights',
            'description' => 'Role for Insights.',
            'permissions' => [
                'Order.Read',
                'OrderLine.Read',
                'Customer.Read',
                'Item.Read',
                'ItemAttribute.Read',
                'DataFilter.Read',
            ],
        ],
        'Marketing' => [
            'name' => 'Marketing',
            'description' => 'Role for Marketing.',
            'permissions' => [
                'WebShopText.Create', 'WebShopText.Read', 'WebShopText.Update', 'WebShopText.Delete',
                'WebShopPage.Create', 'WebShopPage.Read', 'WebShopPage.Update', 'WebShopPage.Delete',
                'Theme.Create', 'Theme.Read', 'Theme.Update', 'Theme.Delete',
                'CompanyEmailTemplate.Create', 'CompanyEmailTemplate.Read', 'CompanyEmailTemplate.Update', 'CompanyEmailTemplate.Delete',
                'CompanyEmailLayout.Create', 'CompanyEmailLayout.Read', 'CompanyEmailLayout.Update', 'CompanyEmailLayout.Delete',
                'Item.Read',
            ],
        ],
    ];

    public function run(): void
    {
        // Resolve the catalog at once. Aliases are the public slugs; legacy pre-migration rows without one
        // are not part of the permission system and must be ignored.
        $permissions = Permission::query()
            ->whereNotNull('Aliases')->where('Aliases', '<>', '')
            ->get(['Id', 'Aliases', 'IsDeveloperOnly']);

        if ($permissions->isEmpty()) {
            $this->command?->warn('DefaultRolePermissionSeeder: Permission catalog is empty. Run PermissionSeeder first.');
            return;
        }

        $idByAlias = $permissions->pluck('Id', 'Aliases')->all();
        $allIds = $permissions->pluck('Id')->all();
        $roleGrantableIds = $permissions->where('IsDeveloperOnly', 0)->pluck('Id')->all();
        // Slugs a non-Developer template is allowed to hold (defense in depth — mirrors the middleware).
        $roleGrantableAliases = array_flip(
            $permissions->where('IsDeveloperOnly', 0)->pluck('Aliases')->all()
        );

        $now = Carbon::now();
        $templatesTouched = 0;
        $grantsInserted = 0;
        $unknownSlugs = [];
        $rejectedDevOnly = [];

        foreach (self::CATALOG as $type => $config) {
            $role = Role::firstOrCreate(
                ['Type' => $type, 'CompanyId' => null],
                ['Name' => $config['name'], 'Description' => $config['description']]
            );
            $templatesTouched++;

            $permissionIds = $this->resolvePermissionIds(
                $type,
                $config['permissions'] ?? [],
                $idByAlias,
                $allIds,
                $roleGrantableIds,
                $roleGrantableAliases,
                $unknownSlugs,
                $rejectedDevOnly
            );

            if (empty($permissionIds)) {
                continue;
            }

            $rows = array_map(fn($pid) => [
                'RoleId' => $role->Id,
                'PermissionId' => $pid,
                'InsertTime' => $now,
                'UpdateTime' => $now,
            ], $permissionIds);

            // UNIQUE(RoleId, PermissionId) makes this idempotent — re-runs leave existing grants untouched.
            foreach (array_chunk($rows, 500) as $chunk) {
                $grantsInserted += DB::table('RolePermission')->insertOrIgnore($chunk);
            }
        }

        $this->command?->info(
            "DefaultRolePermissionSeeder: processed $templatesTouched default-role template(s), "
            . "inserted $grantsInserted new grant(s)."
        );
        if (!empty($unknownSlugs)) {
            $this->command?->warn(
                'DefaultRolePermissionSeeder: skipped catalog slugs with no matching Permission row: '
                . implode(', ', array_unique($unknownSlugs)) . '.'
            );
        }
        if (!empty($rejectedDevOnly)) {
            $this->command?->warn(
                'DefaultRolePermissionSeeder: refused developer-only slugs on non-Developer templates: '
                . implode(', ', array_unique($rejectedDevOnly)) . '.'
            );
        }
    }

    /**
     * Resolve the permission IDs granted to a Type's template.
     *
     * Developer → every permission; Administrator → role-grantable subset; custom Types → the curated
     * slug list, hard-filtered to role-grantable permissions (a non-Developer template must never hold
     * a developer-only grant).
     */
    private function resolvePermissionIds(
        string $type,
        array  $slugs,
        array  $idByAlias,
        array  $allIds,
        array  $roleGrantableIds,
        array  $roleGrantableAliases,
        array  &$unknownSlugs,
        array  &$rejectedDevOnly
    ): array
    {
        if ($type === 'Developer') {
            return $allIds;
        }
        if ($type === 'Administrator') {
            return $roleGrantableIds;
        }

        $ids = [];
        foreach ($slugs as $slug) {
            if (!isset($idByAlias[$slug])) {
                $unknownSlugs[] = $slug;
                continue;
            }
            if (!isset($roleGrantableAliases[$slug])) {
                $rejectedDevOnly[] = $slug;
                continue;
            }
            $ids[] = $idByAlias[$slug];
        }

        return $ids;
    }
}
