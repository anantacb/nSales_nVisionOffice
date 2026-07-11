/*
 * Main navigation arrays
 *
 * 'to' attribute points to the route name, not the path url
 * For Direct Links points the url
 */
export default {
    main: [{
        name: "Developer",
        heading: true,
        roles: ["Developer"]
    },

        {
            name: "Database",
            icon: "fa fa-database",
            roles: ["Developer"],
            sub: [{
                name: "Tables",
                icon: "fa fa-table",
                to: "tables",
                roles: ["Developer"]
            }, {
                name: "Create Table",
                icon: "fa fa-plus",
                to: "create-table",
                roles: ["Developer"]
            }, // {
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
            roles: ["Developer"],
            sub: [{
                name: "Applications",
                icon: "fa fa-mobile",
                to: "applications",
                roles: ["Developer"],
            }, {
                name: "Create Application",
                icon: "fa fa-plus",
                to: "create-application",
                roles: ["Developer"],
            }],
        },

        {
            name: "Setting",
            icon: "fa fa-gear",
            roles: ["Developer"],
            sub: [{
                name: "Settings",
                icon: "fa fa-gears",
                to: "settings",
                roles: ["Developer"],
            }, {
                name: "Create Setting",
                icon: "fa fa-plus",
                to: "create-setting",
                roles: ["Developer"],
            }, {
                name: "Update Setting",
                icon: "fa fa-screwdriver-wrench",
                to: "update-setting",
                roles: ["Developer"],
            },],
        },

        {
            name: "Module",
            icon: "fa fa-box",
            roles: ["Developer"],
            sub: [{
                name: "Modules",
                icon: "fa fa-boxes-stacked",
                to: "modules",
                roles: ["Developer"],
            }, {
                name: "Create Module",
                icon: "fa fa-plus",
                to: "create-module",
                roles: ["Developer"],
            }, {
                name: "Activate Module",
                icon: "fa fa-box-open",
                to: "activate-module",
                roles: ["Developer"],
            }, {
                name: "ModulePackages",
                icon: "fa fa-mobile",
                to: "module-packages",
                roles: ["Developer"],
            }, {
                name: "Create ModulePackage",
                icon: "fa fa-plus",
                to: "create-module-package",
                roles: ["Developer"],
            }],
        },

        {
            name: "Company",
            icon: "fa fa-building-columns",
            roles: ["Developer"],
            sub: [{
                name: "Companies",
                icon: "fa fa-building-columns",
                to: "companies",
                roles: ["Developer"],
            }, {
                name: "Create Company",
                icon: "fa fa-plus",
                to: "create-company",
                roles: ["Developer"],
            }],
        },

        {
            name: "User",
            icon: "fa fa-user-large",
            roles: ["Developer"],
            sub: [{
                name: "Users",
                icon: "fa fa-users",
                to: "users",
                roles: ["Developer"],
            }, {
                name: "Developers",
                icon: "fa fa-chalkboard-user",
                to: "developers",
                roles: ["Developer"],
            }],
        },

        {
            name: "Email Configuration",
            icon: "fa fa-envelope",
            roles: ["Developer"],
            sub: [{
                name: "Email Configurations",
                icon: "fa fa-envelopes-bulk",
                to: "email-configurations",
                roles: ["Developer"],
            }, {
                name: "Create Email Configuration",
                icon: "fa fa-plus",
                to: "create-email-configuration",
                roles: ["Developer"],
            }],
        },

        {
            name: "Data Filter",
            icon: "fa fa-filter",
            roles: ["Developer"],
            sub: [{
                name: "Data Filters",
                icon: "fa fa-filter",
                to: "data-filters",
                roles: ["Developer"],
            }, {
                name: "Create Data Filter",
                icon: "fa fa-plus",
                to: "create-data-filter",
                roles: ["Developer"],
            }],
        },

        {
            name: "Role",
            icon: "fa fa-user-tag",
            roles: ["Developer"],
            sub: [{
                name: "Default Roles",
                icon: "fa fa-star",
                to: "default-roles",
                roles: ["Developer"],
            }, {
                name: "Create Default Role",
                icon: "fa fa-plus",
                to: "create-default-role",
                roles: ["Developer"],
            },],
        },

        {
            name: "Permission",
            icon: "fa fa-shield-alt",
            roles: ["Developer"],
            sub: [{
                name: "Permissions",
                icon: "fa fa-shield-alt",
                to: "permissions",
                roles: ["Developer"],
            }, {
                name: "Create Permission",
                icon: "fa fa-plus",
                to: "create-permission",
                roles: ["Developer"],
            },],
        },

        {
            name: "Language",
            icon: "fa fa-language",
            roles: ["Developer"],
            sub: [{
                name: "Languages",
                icon: "fa fa-language",
                to: "languages",
                roles: ["Developer"],
            }, {
                name: "Create Language",
                icon: "fa fa-plus",
                to: "create-language",
                roles: ["Developer"],
            }, {
                name: "Translations",
                icon: "fa fa-language",
                to: "translations",
                roles: ["Developer"],
            }, {
                name: "Create Translation",
                icon: "fa fa-plus",
                to: "create-translation",
                roles: ["Developer"],
            }],
        },

        {
            name: "Email",
            icon: "fa fa-envelope-open",
            roles: ["Developer"],
            sub: [{
                name: "Layouts",
                icon: "fa fa-envelope",
                to: "email-layouts",
                roles: ["Developer"],
            }, {
                name: "Create Layout",
                icon: "fa fa-plus",
                to: "create-email-layout",
                roles: ["Developer"],
            }, {
                name: "Templates",
                icon: "fa fa-envelope",
                to: "email-templates",
                roles: ["Developer"],
            }, {
                name: "Create Template",
                icon: "fa fa-plus",
                to: "create-email-template",
                roles: ["Developer"],
            }],
        },

        {
            name: "Onboard",
            icon: "fa fa-wand-magic-sparkles",
            roles: ["Developer"],
            sub: [{
                name: "Webshop",
                icon: "fa fa-window-maximize",
                to: "onboardWebshop",
                roles: ["Developer"],
            }, {
                name: "Retailer",
                icon: "fa fa-mobile",
                to: "OnboardRetailer",
                roles: ["Developer"],
            }]
        },

        {
            name: "Themes",
            to: "/themes",
            roles: ["Developer"],
            icon: "fa fa-paint-brush",
            directLink: true,
            targetBlank: false
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
            name: "Cache Clear",
            to: "/cache-clear",
            roles: ["Developer"],
            icon: "fa fa-dumpster-fire",
            directLink: true,
            targetBlank: false
        },


        // Company Menus
        {
            name: "Company",
            heading: true,
            dynamicCompanyName: true
        },

        {
            name: "Staff",
            icon: "fa fa-user-tie",
            roles: [],
            permissions: ["Staff.Read"],
            sub: [{
                name: "Staffs",
                icon: "fa fa-circle-user",
                to: "company-users",
                roles: [],
                permissions: ["Staff.Read"],
            }, {
                name: "Create Staff",
                icon: "fa fa-user-plus",
                to: "create-company-user",
                roles: [],
                permissions: ["Staff.Create"],
            },],
        },

        {
            name: "Roles",
            icon: "fa fa-user-tag",
            roles: [],
            permissions: ["Role.Read"],
            sub: [{
                name: "Roles",
                icon: "fa fa-user-tag",
                to: "roles",
                roles: [],
                permissions: ["Role.Read"],
            }, {
                name: "Create Role",
                icon: "fa fa-plus",
                to: "create-role",
                roles: [],
                permissions: ["Role.Create"],
            }],
        },

        {
            name: "Orders",
            icon: "fa fa-truck-moving",
            moduleSpecific: true,
            moduleName: 'Order',
            roles: [],
            permissions: ['Order.Read'],
            sub: [{
                name: "Orders",
                icon: "fa fa-truck-arrow-right",
                to: "orders",
                moduleSpecific: true,
                moduleName: 'Order',
                roles: [],
                permissions: ['Order.Read'],
            }, {
                name: "Open Orders",
                icon: "fa fa-cart-flatbed",
                to: "open-orders",
                moduleSpecific: true,
                moduleName: 'Order',
                roles: [],
                permissions: ['Order.Read'],
            }, {
                name: "Failed Orders",
                icon: "fa fa-shop-slash",
                to: "failed-orders",
                moduleSpecific: true,
                moduleName: 'Order',
                roles: [],
                permissions: ['Order.Read'],
            },]
        },

        {
            name: "Customer",
            icon: "fa fa-users",
            moduleSpecific: true,
            moduleName: 'Customer',
            roles: [],
            permissions: ['Customer.Read', 'CustomerVisit.Read'],
            sub: [{
                name: "Customers",
                icon: "fa fa-users-line",
                to: "customers",
                moduleSpecific: true,
                moduleName: 'Customer',
                roles: [],
                permissions: ['Customer.Read'],
            }, {
                name: "Create Customer",
                icon: "fa fa-plus",
                to: "create-customer",
                moduleSpecific: true,
                moduleName: 'Customer',
                roles: [],
                permissions: ['Customer.Create'],
            }, {
                name: "Customer Visits",
                icon: "fa fa-people-group",
                to: "customer-visits",
                moduleSpecific: true,
                moduleName: 'CustomerVisit',
                roles: [],
                permissions: ['CustomerVisit.Read'],
            }]
        },

        /*{
            name: "Product",
            icon: "fa fa-gift",
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
                /!*{
                    name: "Create Product",
                    icon: "fa fa-plus",
                    to: "create-customer",
                },*!/
            ]
        },*/

        {
            name: "Language",
            icon: "fa fa-language",
            moduleSpecific: true,
            moduleName: 'Translation',
            roles: [],
            permissions: ['CompanyLanguage.Read', 'CompanyTranslation.Read'],
            sub: [{
                name: "Languages",
                icon: "fa fa-language",
                to: "company-languages",
                moduleSpecific: true,
                moduleName: 'Translation',
                roles: [],
                permissions: ['CompanyLanguage.Read'],
            }, {
                name: "Translations",
                icon: "fa fa-shop-slash",
                to: "company-translations",
                moduleSpecific: true,
                moduleName: 'Translation',
                roles: [],
                permissions: ['CompanyTranslation.Read'],
            }, {
                name: "Create Translation",
                icon: "fa fa-plus",
                to: "create-company-translation",
                moduleSpecific: true,
                moduleName: 'Translation',
                roles: [],
                permissions: ['CompanyTranslation.Create'],
            }]
        },

        {
            name: "Email",
            icon: "fa fa-envelope",
            moduleSpecific: true,
            moduleName: 'CompanyEmail',
            roles: [],
            permissions: ['CompanyEmailLayout.Read', 'CompanyEmailTemplate.Read'],
            sub: [{
                name: "Layouts",
                icon: "fa fa-envelope",
                to: "company-email-layouts",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailLayout.Read'],
            }, {
                name: "Create Layout",
                icon: "fa fa-plus",
                to: "create-company-email-layout",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailLayout.Create'],
            }, {
                name: "Choose Layout",
                icon: "fa fa-copy",
                to: "choose-email-layout",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailLayout.Update'],
            }, {
                name: "Templates",
                icon: "fa fa-envelope",
                to: "company-email-templates",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailTemplate.Read'],
            }, {
                name: "Create New Template",
                icon: "fa fa-plus",
                to: "create-company-email-template",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailTemplate.Create'],
            }, {
                name: "Choose Template",
                icon: "fa fa-copy",
                to: "choose-email-template",
                moduleSpecific: true,
                moduleName: 'CompanyEmail',
                roles: [],
                permissions: ['CompanyEmailTemplate.Update'],
            }],
        },

        {
            name: "Data Filter",
            icon: "fa fa-filter-circle-dollar",
            roles: [],
            permissions: ["DataFilter.Read"],
            sub: [{
                name: "Data Filters",
                icon: "fa fa-filter-circle-dollar",
                to: "company-data-filters",
                roles: [],
                permissions: ["DataFilter.Read"],
            }, {
                name: "Create Data Filter",
                icon: "fa fa-plus",
                to: "create-company-data-filter",
                roles: [],
                permissions: ["DataFilter.Create"],
            }],
        },

        {
            name: "Email Configurations",
            icon: "fa fa-envelopes-bulk",
            to: "company-email-configurations",
            roles: ["Developer"],
        },

    ]
};
