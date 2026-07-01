<?php

namespace Database\Seeders;

use App\Models\Office\Module;
use App\Models\Office\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    // Each entry: { module: Module.Name to resolve ModuleId, permissions: slug prefixes, actions: verbs }.
    // The slug ("{permission}.{action}", e.g., EmailLayout.Create) is the public contract for middleware
    // and frontend gates; `module` is the coarser taxonomy used purely for grouping in the UI grid.
    private const array CATALOG = [
        // Developer-only permissions (IsDeveloperOnly = 1). Administrator does NOT bypass these.
        // Covers every core/platform module (Module.Type = 'Core') plus global system artifacts —
        // only the Developer role-Type can ever reach them.
        'developerOnly' => [
            ['module' => 'Table', 'permissions' => ['Table', 'TableField', 'TableIndex'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Application', 'permissions' => ['Application', 'ApplicationModule'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'ModuleSetting', 'permissions' => ['ModuleSetting'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Module', 'permissions' => ['Module'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'ModulePackage', 'permissions' => ['ModulePackage'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Company', 'permissions' => ['Company'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'EmailConfiguration', 'permissions' => ['EmailConfiguration'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Role', 'permissions' => ['DefaultRole', 'DefaultPermission'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Language', 'permissions' => ['Language', 'Translation'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Email', 'permissions' => ['EmailLayout', 'EmailTemplate'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'DocumentApi', 'permissions' => ['DocumentApi'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Theme', 'permissions' => ['Theme'],
                'actions' => ['Create', 'Delete']],
            ['module' => 'Translation', 'permissions' => ['Translation'],
                'actions' => ['Read', 'Update']],
        ],
        // Role-grantable permissions (IsDeveloperOnly = 0). Administrator bypasses implicitly;
        // other role-Types must hold the explicit grant.
        'roleGrantable' => [
            ['module' => '', 'permissions' => ['CompanyInformation'],
                'actions' => ['Read', 'Update']],

            ['module' => 'Order', 'permissions' => ['Order'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Subscription', 'permissions' => ['Subscription'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Claim', 'permissions' => ['Claim'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Item', 'permissions' => ['Product'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Pricegroup', 'permissions' => ['Pricegroup'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => '', 'permissions' => ['PriceDiscount'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Campaign', 'permissions' => ['Campaign'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Promotion', 'permissions' => ['Promotion'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'BuyXY', 'permissions' => ['BuyXY'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'PIM', 'permissions' => ['PIM'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Customer', 'permissions' => ['Customer'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'CustomerVisit', 'permissions' => ['CustomerVisit'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'CustomerAssortment', 'permissions' => ['CustomerAssortment'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'SalesPlanner', 'permissions' => ['SalesPlanner'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Budget', 'permissions' => ['Budget'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Lead', 'permissions' => ['Lead'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'PdfCatalogue', 'permissions' => ['PdfCatalogue'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'User', 'permissions' => ['Staff'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Role', 'permissions' => ['Role'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Role', 'permissions' => ['Permission'],
                'actions' => ['Read', 'Update']],

            ['module' => 'WSUser', 'permissions' => ['User'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'BrandSelector', 'permissions' => ['BrandSelector'],
                'actions' => ['Update']],

            ['module' => 'Itemgroup', 'permissions' => ['Category'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'WSShipping', 'permissions' => ['Shipping'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'WSVoucher', 'permissions' => ['Voucher'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Inspiration', 'permissions' => ['Inspiration'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Theme', 'permissions' => ['Theme'],
                'actions' => ['Read', 'Update']],

            ['module' => 'Translation', 'permissions' => ['CompanyLanguage'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Translation', 'permissions' => ['CompanyTranslation'],
                'actions' => ['Read', 'Update']],

            ['module' => 'CompanyEmail', 'permissions' => ['CompanyEmailLayout', 'CompanyEmailTemplate'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'DataFilter', 'permissions' => ['DataFilter'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Notification', 'permissions' => ['Notification'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Notification', 'permissions' => ['Notification'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Live', 'permissions' => ['Live'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Brand', 'permissions' => ['Brand'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => '', 'permissions' => ['ImportFileData'],
                'actions' => ['Create', 'Read']],

        ],
    ];

    private const array DESCRIPTION_TEMPLATES = [
        'Create' => 'Create new %s records',
        'Read' => 'View %s records',
        'Update' => 'Modify existing %s records',
        'Delete' => 'Delete %s records',
    ];

    public function run(): void
    {
        $moduleIdsByName = Module::pluck('Id', 'Name')->all();

        $nullModuleNames = [];
        $inserted = 0;

        $insert = function (
            string $permissionName,
            string $action,
            int    $isDeveloperOnly,
            ?int   $moduleId
        ) use (&$inserted): void {
            $aliases = "$permissionName.$action";
            $description = sprintf(
                self::DESCRIPTION_TEMPLATES[$action] ?? '%s',
                $permissionName
            );

            // Insert only: existing rows (matched by Aliases) are left untouched so their Ids —
            // and the RolePermission grants that reference them — stay intact.
            $permission = Permission::firstOrCreate(
                ['Aliases' => $aliases],
                [
                    'Name' => $action,
                    'ModuleId' => $moduleId,
                    'Description' => $description,
                    'IsDeveloperOnly' => $isDeveloperOnly,
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $inserted++;
            }
        };

        foreach (self::CATALOG as $devOnlyKey => $groups) {
            $isDeveloperOnly = $devOnlyKey === 'developerOnly' ? 1 : 0;

            foreach ($groups as $group) {
                $moduleName = $group['module'] ?? null;
                if ($moduleName !== null && !isset($moduleIdsByName[$moduleName])) {
                    $nullModuleNames[] = $moduleName;
                }
                $moduleId = $moduleName !== null ? ($moduleIdsByName[$moduleName] ?? null) : null;

                foreach ($group['permissions'] as $permissionName) {
                    foreach ($group['actions'] as $action) {
                        $insert($permissionName, $action, $isDeveloperOnly, $moduleId);
                    }
                }
            }
        }

        $this->command?->info("PermissionSeeder: inserted $inserted new permission row(s).");
        if (!empty($nullModuleNames)) {
            $this->command?->info(
                'PermissionSeeder: seeded with ModuleId = NULL (no matching Module row): '
                . implode(', ', array_unique($nullModuleNames))
                . '. Expected for module-less ops; otherwise check the name matches a Module.Name.'
            );
        }
    }
}
