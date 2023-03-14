/*
 * Main and demo navigation arrays
 *
 * 'to' attribute points to the route name, not the path url
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
                    icon: "fa fa-folder-plus",
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
                    icon: "fa fa-folder-plus",
                    to: "settings",
                },
                {
                    name: "Create Setting",
                    icon: "fa fa-folder-plus",
                    to: "create-setting",
                },
                {
                    name: "Update Setting",
                    icon: "fa fa-gear",
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
                    icon: "fa fa-gear",
                    to: "modules",
                },
                {
                    name: "Create Module",
                    icon: "fa fa-gear",
                    to: "create-module",
                },
                {
                    name: "Activate Module",
                    icon: "fa fa-folder-plus",
                    to: "activate-module",
                }
            ],
        },

        {
            name: "Company",
            icon: "fa fa-box",
            subActivePaths: ["company"],
            sub: [
                {
                    name: "Companies",
                    icon: "fa fa-gear",
                    to: "companies",
                },
                {
                    name: "Create Company",
                    icon: "fa fa-gear",
                    to: "create-company",
                }
            ],
        },

        {
            name: "User",
            icon: "fa fa-box",
            subActivePaths: ["user"],
            sub: [
                {
                    name: "Users",
                    icon: "fa fa-gear",
                    to: "users",
                },
                {
                    name: "Create Company User",
                    icon: "fa fa-gear",
                    to: "create-company-user",
                }
            ],
        },

        {
            name: "Email Configuration",
            icon: "fa fa-database",
            subActivePaths: "email-configuration",
            sub: [
                {
                    name: "Email Configurations",
                    icon: "fa fa-table",
                    to: "email-configurations",
                },
                {
                    name: "Create Email Configuration",
                    icon: "fa fa-folder-plus",
                    to: "create-email-configuration",
                }
            ],
        },
    ]
};
