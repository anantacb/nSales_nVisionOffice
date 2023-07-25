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
        },

        {
            name: "Database",
            icon: "fa fa-database",
            subActivePaths: "database",
            sub: [
                {
                    name: "Tables",
                    icon: "fa fa-table",
                    to: "tables",
                },
                {
                    name: "Create Table",
                    icon: "fa fa-plus",
                    to: "create-table",
                }
            ],
        },

        {
            name: "Setting",
            icon: "fa fa-gear",
            subActivePaths: "setting",
            sub: [
                {
                    name: "Settings",
                    icon: "fa fa-gears",
                    to: "settings",
                },
                {
                    name: "Create Setting",
                    icon: "fa fa-plus",
                    to: "create-setting",
                },
                {
                    name: "Update Setting",
                    icon: "fa fa-screwdriver-wrench",
                    to: "update-setting",
                },
            ],
        },

        {
            name: "Module",
            icon: "fa fa-box",
            subActivePaths: "module",
            sub: [
                {
                    name: "Modules",
                    icon: "fa fa-boxes-stacked",
                    to: "modules",
                },
                {
                    name: "Create Module",
                    icon: "fa fa-plus",
                    to: "create-module",
                },
                {
                    name: "Activate Module",
                    icon: "fa fa-box-open",
                    to: "activate-module",
                }
            ],
        },

        {
            name: "Company",
            icon: "fa fa-building-columns",
            subActivePaths: ["company"],
            sub: [
                {
                    name: "Companies",
                    icon: "fa fa-building-columns",
                    to: "companies",
                },
                {
                    name: "Create Company",
                    icon: "fa fa-plus",
                    to: "create-company",
                }
            ],
        },

        {
            name: "User",
            icon: "fa fa-user-large",
            subActivePaths: ["user"],
            sub: [
                {
                    name: "Users",
                    icon: "fa fa-users",
                    to: "users",
                },
                {
                    name: "Developers",
                    icon: "fa fa-chalkboard-user",
                    to: "developers",
                },
                {
                    name: "Company Users",
                    icon: "fa fa-circle-user",
                    to: "company-users",
                },
                {
                    name: "Create Company User",
                    icon: "fa fa-user-plus",
                    to: "create-company-user",
                }
            ],
        },

        {
            name: "Email Configuration",
            icon: "fa fa-envelope",
            subActivePaths: "email-configuration",
            sub: [
                {
                    name: "Email Configurations",
                    icon: "fa fa-envelopes-bulk",
                    to: "email-configurations",
                },
                {
                    name: "Company Email Configurations",
                    icon: "fa fa-envelopes-bulk",
                    to: "company-email-configurations",
                },
                {
                    name: "Create Email Configuration",
                    icon: "fa fa-plus",
                    to: "create-email-configuration",
                }
            ],
        },

        {
            name: "Data Filter",
            icon: "fa fa-filter",
            subActivePaths: "data-filter",
            sub: [
                {
                    name: "Data Filters",
                    icon: "fa fa-filter",
                    to: "data-filters",
                },
                {
                    name: "Company Data Filters",
                    icon: "fa fa-filter-circle-dollar",
                    to: "company-data-filters",
                },
                {
                    name: "Create Data Filter",
                    icon: "fa fa-plus",
                    to: "create-data-filter",
                }
            ],
        },

        {
            name: "Role",
            icon: "fa fa-user-tag",
            subActivePaths: ["role"],
            sub: [
                {
                    name: "Roles",
                    icon: "fa fa-user-tag",
                    to: "roles",
                },
                {
                    name: "Create Role",
                    icon: "fa fa-plus",
                    to: "create-role",
                }
            ],
        },

        {
            name: "Log Viewer",
            to: "/log-viewer",
            icon: "far fa-eye",
            directLink: true,
            targetBlank: true
        },

        {
            name: "Company",
            heading: true,
        },

        {
            name: "Orders",
            icon: "fa fa-file-lines",
            to: 'orders'
        },
    ]
};
