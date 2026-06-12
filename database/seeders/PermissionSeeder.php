<?php

namespace Database\Seeders;

use App\Models\Office\ApplicationModule;
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
            ['module' => 'Module', 'permissions' => ['Module'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'ModuleSetting', 'permissions' => ['ModuleSetting'],
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
        ],
        // Role-grantable permissions (IsDeveloperOnly = 0). Administrator bypasses implicitly;
        // other role-Types must hold the explicit grant.
        'roleGrantable' => [
            ['module' => 'Order', 'permissions' => ['Order'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Claim', 'permissions' => ['Claim'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Subscription', 'permissions' => ['Subscription'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Item', 'permissions' => ['Product'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'PriceDiscountAxNav', 'permissions' => ['PriceDiscountAxNav'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'PriceDiscountC5', 'permissions' => ['PriceDiscountC5'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Pricegroup', 'permissions' => ['Pricegroup'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'PriceDiscountAX', 'permissions' => ['PriceDiscountAX'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'PriceDiscountNAV', 'permissions' => ['PriceDiscountNAV'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'CustomerPricing', 'permissions' => ['CustomerPricing'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],

            ['module' => 'Campaign', 'permissions' => ['Campaign'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Promotion', 'permissions' => ['Promotion'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'BuyXY', 'permissions' => ['BuyXY'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'PIM', 'permissions' => ['PIM'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],


            ['module' => 'CompanyEmail', 'permissions' => ['CompanyEmailLayout', 'CompanyEmailTemplate'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Translation', 'permissions' => ['CompanyLanguage', 'CompanyTranslation'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'DataFilter', 'permissions' => ['DataFilter'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Theme', 'permissions' => ['Theme'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Customer', 'permissions' => ['Customer'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'Item', 'permissions' => ['Item', 'ItemAttribute'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'WSPage', 'permissions' => ['WebShopText', 'WebShopPage'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
            ['module' => 'WSUser', 'permissions' => ['WebShopUser'],
                'actions' => ['Create', 'Read', 'Update', 'Delete']],
        ],
    ];

    private const array DESCRIPTION_TEMPLATES = [
        'Create' => 'Create new %s records',
        'Read' => 'View %s records',
        'Update' => 'Modify existing %s records',
        'Delete' => 'Delete %s records',
        'Manage' => 'Manage %s',
    ];

    // One `{Module}.Manage` permission per module, grouped by Module.Type. Snapshot of the
    // Module table (slug-safe names only; "CreateCustomer ", "Item Translation", "My Documents"
    // are excluded as they contain spaces). ModuleId is resolved by name at run time. When new
    // modules are added, append them to the matching list below.
    private const array MANAGE_CORE = [        // developer-only (IsDeveloperOnly = 1)
        'Application', 'ApplicationModule', 'Company', 'CompanySignup', 'CoreSetting', 'DataFilter',
        'DataTypesEntity', 'DataTypesGeneric', 'Device', 'Email', 'EmailConfiguration',
        'ImageHostAccount', 'JiraWorklog', 'Language', 'LocalResource', 'LocalText', 'Mapping',
        'Module', 'ModulePackage', 'ModuleSetting', 'QuickBooks', 'Report', 'Role', 'Schedule',
        'ScheduleScript', 'Script', 'ScriptConfig', 'ScriptMapping', 'Setting', 'Table', 'TableField',
        'TableIndex', 'Text', 'Theme', 'User', 'UserInvitation',
    ];
    private const array MANAGE_STANDARD = [    // role-grantable (IsDeveloperOnly = 0)
        'ActivityLog', 'Brand', 'Budget', 'BuyXY', 'Calendar', 'Campaign', 'Claim', 'Commission',
        'CompanyEmail', 'Country', 'Customer', 'CustomerAssortment', 'CustomerSignup', 'CustomerVisit',
        'DeliveryPeriod', 'DepositAndPackaging', 'DocumentApi', 'Dropbox', 'Flag', 'GeoRestrictions',
        'HomeScreen', 'Inspiration', 'Item', 'Itemattribute', 'ItemDeleted', 'Itemgroup', 'ItemStock',
        'Lead', 'Linesheet', 'Live', 'Notification', 'Order', 'Orderline', 'PaymentAltapay',
        'PdfCatalogue', 'PIM', 'Promotion', 'Purchase', 'RepBudget', 'Retailer', 'RetailPage',
        'RetailUser', 'SalesPlanner', 'SalesRadar', 'SalesRepBudget', 'Season', 'Showroom',
        'Subscription', 'Survey', 'Survey2', 'TestModule', 'Translation', 'Vendor', 'VisitPlanner',
        'WebShop', 'WebShopCustomStyling', 'WebShopTextTemp', 'WSLanguage', 'WSPage', 'WSPaymentGateway',
        'WSPresentation', 'WSReturnOrder', 'WSSEO', 'WSShipping', 'WSSocialLogin', 'WSUser', 'WSVoucher',
    ];
    private const array MANAGE_EXTENSION = [   // role-grantable (IsDeveloperOnly = 0)
        'Bex', 'BrandSelector', 'BusinessCentral', 'BusinessIntelligence', 'CameraScanner', 'Canvas',
        'Connector', 'CRM', 'CustomData', 'CustomerAddress', 'CustomerPricing', 'CustomerPurchaseHistory',
        'CustomerSalesRep', 'd365', 'DatabaseLog', 'Documents', 'Dropshipping', 'DynamicWeb', 'Economic',
        'ExcelUpload', 'FAQ', 'Firebase', 'GoogleAnalytics', 'ItemAssortment', 'ItemCollection',
        'ItemExport', 'ItemgroupTemp', 'ItemShoppingFeed', 'ItemText', 'ItemVariant',
        'ItemVariantDescription', 'ItemVariantStock', 'ItemVariantTemp', 'Klaviyo', 'MultiOrder',
        'OrderType', 'Payment', 'Perfion', 'Planogram', 'Plytix', 'PriceDiscountAX', 'PriceDiscountAxNav',
        'PriceDiscountC5', 'PriceDiscountNAV', 'Pricegroup', 'Ractbeat', 'Shopify', 'StockDelivery',
        'StripePaymentGateway', 'StructPIM', 'Supplier', 'SuppTranslationTemp', 'TemplateDesigner',
        'Uniconta', 'WebShopItemAttribute', 'WooCommerce',
    ];

    // Modules typed 'Core' whose `{Module}.Manage` permission must nevertheless stay
    // role-grantable (IsDeveloperOnly = 0), matching their historical CRUD catalog entries.
    private const array MANAGE_ROLE_GRANTABLE_OVERRIDES = ['DataFilter', 'Theme'];

    public function run(): void
    {
        $moduleIdsByName = Module::pluck('Id', 'Name')->all();
        $applicationIdByModuleId = ApplicationModule::query()
            ->select(['ModuleId', 'ApplicationId'])
            ->get()
            ->groupBy('ModuleId')
            ->map(fn($rows) => $rows->first()->ApplicationId)
            ->all();

        $nullModuleNames = [];
        $upserted = 0;

        $upsert = function (
            string $permissionName,
            string $action,
            int $isDeveloperOnly,
            ?int $moduleId,
            ?int $applicationId
        ) use (&$upserted): void {
            $aliases = "$permissionName.$action";
            $description = sprintf(
                self::DESCRIPTION_TEMPLATES[$action] ?? '%s',
                $permissionName
            );

            Permission::updateOrCreate(
                ['Aliases' => $aliases],
                [
                    'Name' => $action,
                    'ApplicationId' => $applicationId,
                    'ModuleId' => $moduleId,
                    'Description' => $description,
                    'IsDeveloperOnly' => $isDeveloperOnly,
                ]
            );
            $upserted++;
        };

        foreach (self::CATALOG as $devOnlyKey => $groups) {
            $isDeveloperOnly = $devOnlyKey === 'developerOnly' ? 1 : 0;

            foreach ($groups as $group) {
                $moduleName = $group['module'] ?? null;
                if ($moduleName !== null && !isset($moduleIdsByName[$moduleName])) {
                    $nullModuleNames[] = $moduleName;
                }
                $moduleId = $moduleName !== null ? ($moduleIdsByName[$moduleName] ?? null) : null;
                $applicationId = $moduleId !== null ? ($applicationIdByModuleId[$moduleId] ?? null) : null;

                foreach ($group['permissions'] as $permissionName) {
                    foreach ($group['actions'] as $action) {
                        $upsert($permissionName, $action, $isDeveloperOnly, $moduleId, $applicationId);
                    }
                }
            }
        }

        // One `{Module}.Manage` permission per module, from the explicit lists above.
        // Core => developer-only; Standard/Extension => role-grantable (with explicit overrides).
        // ModuleId resolved by name; absent modules still seed a row with ModuleId = NULL.
        $manageGroups = [
            1 => self::MANAGE_CORE,                                            // developer-only
            0 => array_merge(self::MANAGE_STANDARD, self::MANAGE_EXTENSION),   // role-grantable
        ];
        foreach ($manageGroups as $devOnly => $names) {
            foreach ($names as $name) {
                $isDeveloperOnly = in_array($name, self::MANAGE_ROLE_GRANTABLE_OVERRIDES, true)
                    ? 0
                    : $devOnly;
                if (!isset($moduleIdsByName[$name])) {
                    $nullModuleNames[] = $name;
                }
                $moduleId = $moduleIdsByName[$name] ?? null;
                $applicationId = $moduleId !== null ? ($applicationIdByModuleId[$moduleId] ?? null) : null;
                $upsert($name, 'Manage', $isDeveloperOnly, $moduleId, $applicationId);
            }
        }

        $this->command?->info("PermissionSeeder: upserted $upserted permission rows.");
        if (!empty($nullModuleNames)) {
            $this->command?->info(
                'PermissionSeeder: seeded with ModuleId = NULL (no matching Module row): '
                . implode(', ', array_unique($nullModuleNames))
                . '. Expected for module-less ops; otherwise check the name matches a Module.Name.'
            );
        }
    }
}
