/*
 * Main navigation arrays
 *
 * 'to' attribute points to the route name, not the path url
 * For Direct Links points the url
 */

export default {
    main: [
        {
            name: "Developer",
            heading: true,
            roles: ["Developer"]
        },

        {
            name: "Database",
            icon: "fa fa-database",
            subActivePaths: "database",
            roles: ["Developer"],
            sub: [
                {
                    name: "Tables",
                    icon: "fa fa-table",
                    to: "tables",
                    roles: ["Developer"]
                },
                {
                    name: "Create Table",
                    icon: "fa fa-plus",
                    to: "create-table",
                    roles: ["Developer"]
                },
                // {
                //     name: "Copy To Dev",
                //     icon: "fa fa-copy",
                //     to: "copy-database",
                //     roles: ["Developer"]
                // }
            ],
        },

        {
            name: "Application",
            icon: "fa fa-mobile",
            subActivePaths: "application",
            roles: ["Developer"],
            sub: [
                {
                    name: "Applications",
                    icon: "fa fa-mobile",
                    to: "applications",
                    roles: ["Developer"],
                },
                {
                    name: "Create Application",
                    icon: "fa fa-plus",
                    to: "create-application",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Setting",
            icon: "fa fa-gear",
            subActivePaths: "setting",
            roles: ["Developer"],
            sub: [
                {
                    name: "Settings",
                    icon: "fa fa-gears",
                    to: "settings",
                    roles: ["Developer"],
                },
                {
                    name: "Create Setting",
                    icon: "fa fa-plus",
                    to: "create-setting",
                    roles: ["Developer"],
                },
                {
                    name: "Update Setting",
                    icon: "fa fa-screwdriver-wrench",
                    to: "update-setting",
                    roles: ["Developer"],
                },
            ],
        },

        {
            name: "Module",
            icon: "fa fa-box",
            subActivePaths: ["module", "module-package"],
            roles: ["Developer"],
            sub: [
                {
                    name: "Modules",
                    icon: "fa fa-boxes-stacked",
                    to: "modules",
                    roles: ["Developer"],
                },
                {
                    name: "Create Module",
                    icon: "fa fa-plus",
                    to: "create-module",
                    roles: ["Developer"],
                },
                {
                    name: "Activate Module",
                    icon: "fa fa-box-open",
                    to: "activate-module",
                    roles: ["Developer"],
                },

                {
                    name: "ModulePackages",
                    icon: "fa fa-mobile",
                    to: "module-packages",
                    roles: ["Developer"],
                },
                {
                    name: "Create ModulePackage",
                    icon: "fa fa-plus",
                    to: "create-module-package",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Company",
            icon: "fa fa-building-columns",
            subActivePaths: ["company"],
            roles: ["Developer"],
            sub: [
                {
                    name: "Companies",
                    icon: "fa fa-building-columns",
                    to: "companies",
                    roles: ["Developer"],
                },
                {
                    name: "Create Company",
                    icon: "fa fa-plus",
                    to: "create-company",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "User",
            icon: "fa fa-user-large",
            subActivePaths: ["user"],
            roles: ["Developer"],
            sub: [
                {
                    name: "Users",
                    icon: "fa fa-users",
                    to: "users",
                    roles: ["Developer"],
                },
                {
                    name: "Developers",
                    icon: "fa fa-chalkboard-user",
                    to: "developers",
                    roles: ["Developer"],
                },
                {
                    name: "Company Users",
                    icon: "fa fa-circle-user",
                    to: "company-users",
                    roles: ["Developer"],
                },
                {
                    name: "Create Company User",
                    icon: "fa fa-user-plus",
                    to: "create-company-user",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Email Configuration",
            icon: "fa fa-envelope",
            subActivePaths: "email-configuration",
            roles: ["Developer"],
            sub: [
                {
                    name: "Email Configurations",
                    icon: "fa fa-envelopes-bulk",
                    to: "email-configurations",
                    roles: ["Developer"],
                },
                {
                    name: "Company Email Configurations",
                    icon: "fa fa-envelopes-bulk",
                    to: "company-email-configurations",
                    roles: ["Developer"],
                },
                {
                    name: "Create Email Configuration",
                    icon: "fa fa-plus",
                    to: "create-email-configuration",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Data Filter",
            icon: "fa fa-filter",
            subActivePaths: "data-filter",
            roles: ["Developer"],
            sub: [
                {
                    name: "Data Filters",
                    icon: "fa fa-filter",
                    to: "data-filters",
                    roles: ["Developer"],
                },
                {
                    name: "Company Data Filters",
                    icon: "fa fa-filter-circle-dollar",
                    to: "company-data-filters",
                    roles: ["Developer"],
                },
                {
                    name: "Create Data Filter",
                    icon: "fa fa-plus",
                    to: "create-data-filter",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Role",
            icon: "fa fa-user-tag",
            subActivePaths: ["role"],
            roles: ["Developer"],
            sub: [
                {
                    name: "Roles",
                    icon: "fa fa-user-tag",
                    to: "roles",
                    roles: ["Developer"],
                },
                {
                    name: "Create Role",
                    icon: "fa fa-plus",
                    to: "create-role",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Language",
            icon: "fa fa-language",
            subActivePaths: ["language", 'translation'],
            roles: ["Developer"],
            sub: [
                {
                    name: "Languages",
                    icon: "fa fa-language",
                    to: "languages",
                    roles: ["Developer"],
                },
                {
                    name: "Create Language",
                    icon: "fa fa-plus",
                    to: "create-language",
                    roles: ["Developer"],
                },
                {
                    name: "Translations",
                    icon: "fa fa-language",
                    to: "translations",
                    roles: ["Developer"],
                },
                {
                    name: "Create Translation",
                    icon: "fa fa-plus",
                    to: "create-translation",
                    roles: ["Developer"],
                }
            ],
        },

        {
            name: "Log Viewer",
            to: "/log-viewer",
            roles: ["Developer"],
            icon: "far fa-eye",
            directLink: true,
            targetBlank: true
        },

        {
            name: "Horizon",
            to: "/horizon",
            roles: ["Developer"],
            icon: "fa fa-tachometer-alt",
            directLink: true,
            targetBlank: true
        },

        {
            name: "Company",
            heading: true,
            roles: ["Developer", "Administrator", "Employee"],
        },

        {
            name: "Orders",
            icon: "fa fa-truck-moving",
            subActivePaths: ["order"],
            moduleSpecific: true,
            moduleName: 'Order',
            roles: ["Developer", "Administrator", "Employee"],
            sub: [
                {
                    name: "Orders",
                    icon: "fa fa-truck-arrow-right",
                    to: "orders",
                    moduleSpecific: true,
                    moduleName: 'Order',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Open Orders",
                    icon: "fa fa-cart-flatbed",
                    to: "open-orders",
                    moduleSpecific: true,
                    moduleName: 'Order',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Failed Orders",
                    icon: "fa fa-shop-slash",
                    to: "failed-orders",
                    moduleSpecific: true,
                    moduleName: 'Order',
                    roles: ["Developer", "Administrator", "Employee"],
                },
            ]
        },

        {
            name: "Customer",
            icon: "fa fa-users",
            subActivePaths: ["customer", "customer-visit"],
            moduleSpecific: true,
            moduleName: 'Customer',
            roles: ["Developer", "Administrator", "Employee"],
            sub: [
                {
                    name: "Customers",
                    icon: "fa fa-users-line",
                    to: "customers",
                    moduleSpecific: true,
                    moduleName: 'Customer',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Create Customer",
                    icon: "fa fa-plus",
                    to: "create-customer",
                    moduleSpecific: true,
                    moduleName: 'Customer',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Customer Visits",
                    icon: "fa fa-people-group",
                    to: "customer-visits",
                    moduleSpecific: true,
                    moduleName: 'CustomerVisit',
                    roles: ["Developer", "Administrator", "Employee"],
                }
            ]
        },
        {
            name: "Product",
            icon: "fa fa-gift",
            subActivePaths: ["product"],
            moduleSpecific: true,
            moduleName: 'Item',
            roles: ["Developer", "Administrator", "Employee"],
            sub: [
                {
                    name: "Products",
                    icon: "fa fa-gifts",
                    to: "items",
                    moduleSpecific: true,
                    moduleName: 'Item',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                /*{
                    name: "Create Product",
                    icon: "fa fa-plus",
                    to: "create-customer",
                },*/
            ]
        },

        {
            name: "Language",
            icon: "fa fa-language",
            subActivePaths: ["company-language", "company-translation"],
            moduleSpecific: true,
            moduleName: 'Translation',
            roles: ["Developer", "Administrator", "Employee"],
            sub: [
                {
                    name: "Languages",
                    icon: "fa fa-language",
                    to: "company-languages",
                    moduleSpecific: true,
                    moduleName: 'Translation',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Translations",
                    icon: "fa fa-shop-slash",
                    to: "company-translations",
                    moduleSpecific: true,
                    moduleName: 'Translation',
                    roles: ["Developer", "Administrator", "Employee"],
                },
                {
                    name: "Create Translation",
                    icon: "fa fa-plus",
                    to: "create-company-translation",
                    moduleSpecific: true,
                    moduleName: 'Translation',
                    roles: ["Developer", "Administrator", "Employee"],
                }
            ]
        },
    ]
};
