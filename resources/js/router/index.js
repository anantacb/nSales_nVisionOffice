import {createRouter, createWebHistory} from "vue-router";
import NProgress from "nprogress/nprogress.js";

import LayoutBackend from "@/layouts/variations/Backend.vue";
import LayoutSimple from "@/layouts/variations/Simple.vue";
import {useAuthStore} from "@/stores/authStore";

const Login = () => import("@/views/auth/Login.vue");
const Home = () => import("@/views/Home.vue");

const Tables = () => import("@/views/database/Tables/Tables.vue");
const CreateTable = () => import("@/views/database/CreateTable/CreateTable.vue");
const TableFields = () => import("@/views/database/ManageTableFields/TableFields.vue");

const ModuleSettings = () => import("@/views/setting/ModuleSettings/ModuleSettings.vue");
const CreateModuleSetting = () => import("@/views/setting/CreateModuleSetting.vue");
const EditModuleSetting = () => import("@/views/setting/EditModuleSetting.vue");
const UpdateSetting = () => import("@/views/setting/UpdateSetting.vue");

const Modules = () => import("@/views/module/Modules/Modules.vue");
const CreateModule = () => import("@/views/module/CreateModule.vue");
const EditModule = () => import("@/views/module/EditModule.vue");
const ActivateModule = () => import("@/views/module/ActivateModule.vue");

const Companies = () => import("@/views/company/Companies/Companies.vue");
const CreateCompany = () => import("@/views/company/CreateCompany.vue");
const EditCompany = () => import("@/views/company/EditCompany.vue");

const Users = () => import("@/views/user/Users/Users.vue");
const EditUser = () => import("@/views/user/EditUser/EditUser.vue");
const Developers = () => import("@/views/user/Developers/Developers.vue");
const CompanyUsers = () => import("@/views/user/CompanyUsers/CompanyUsers.vue");
const CreateCompanyUser = () => import("@/views/user/CreateCompanyUser.vue");
const EditCompanyUser = () => import("@/views/user/EditCompanyUser.vue");

const CreateEmailConfiguration = () => import('@/views/email-configuration/CreateEmailConfiguration.vue')
const EmailConfigurations = () => import('@/views/email-configuration/EmailConfigurations/EmailConfigurations.vue')
const CompanyEmailConfigurations = () => import('@/views/email-configuration/CompanyEmailConfigurations/CompanyEmailConfigurations.vue')
const EditEmailConfiguration = () => import('@/views/email-configuration/EditEmailConfiguration.vue');

const CreateDataFilter = () => import('@/views/data-filter/CreateDataFilter.vue')
const DataFilters = () => import('@/views/data-filter/DataFilters/DataFilters.vue')
const EditDataFilter = () => import('@/views/data-filter/EditDataFilter.vue');

const CompanyDataFilters = () => import('@/views/data-filter/CompanyDataFilters/CompanyDataFilters.vue')

const Roles = () => import('@/views/roles/Roles/Roles.vue');
const CreateRole = () => import('@/views/roles/CreateRole.vue');
const EditRole = () => import('@/views/roles/EditRole.vue');


const Orders = () => import('@/views/order/Orders/Orders.vue')

const NotFound = () => import('@/views/404View.vue');

const routes = [
    {
        path: "",
        component: LayoutBackend,
        children: [
            {
                path: "",
                name: "home",
                component: Home,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "database/tables",
                name: "tables",
                component: Tables,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "database/table/create",
                name: "create-table",
                component: CreateTable,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "database/table/:id/table-fields",
                name: "manage-table-fields",
                component: TableFields,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },


            {
                path: "settings/settings",
                name: "settings",
                component: ModuleSettings,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "setting/create",
                name: "create-setting",
                component: CreateModuleSetting,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "setting/:id/edit",
                name: "edit-setting",
                component: EditModuleSetting,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "setting/update",
                name: "update-setting",
                component: UpdateSetting,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },


            {
                path: "module/modules",
                name: "modules",
                component: Modules,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "module/create",
                name: "create-module",
                component: CreateModule,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "module/:id/edit",
                name: "edit-module",
                component: EditModule,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "module/activate",
                name: "activate-module",
                component: ActivateModule,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },


            {
                path: "company/companies",
                name: "companies",
                component: Companies,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "company/create",
                name: "create-company",
                component: CreateCompany,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "company/:id/edit",
                name: "edit-company",
                component: EditCompany,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },


            {
                path: "user/users",
                name: "users",
                component: Users,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "user/:id/edit",
                name: "edit-user",
                component: EditUser,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "user/developers",
                name: "developers",
                component: Developers,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "user/company-users",
                name: "company-users",
                component: CompanyUsers,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "user/company-user/create",
                name: "create-company-user",
                component: CreateCompanyUser,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "user/company-user/:id/edit",
                name: "edit-company-user",
                component: EditCompanyUser,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },

            {
                path: "email-configuration/email-configurations",
                name: "email-configurations",
                component: EmailConfigurations,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "email-configuration/company-email-configurations",
                name: "company-email-configurations",
                component: CompanyEmailConfigurations,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "email-configuration/create",
                name: "create-email-configuration",
                component: CreateEmailConfiguration,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "email-configuration/:id/edit",
                name: "edit-email-configuration",
                component: EditEmailConfiguration,
                meta: {
                    authenticated: true,
                    company_specific: false
                },
                beforeEnter: (to, from) => {
                    if (['email-configurations', 'company-email-configurations'].includes(from.name)) {
                        localStorage.setItem('email-configuration-back-route', from.name);
                    }
                    return true;
                },
            },

            {
                path: "data-filter/data-filters",
                name: "data-filters",
                component: DataFilters,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "data-filter/create",
                name: "create-data-filter",
                component: CreateDataFilter,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },
            {
                path: "data-filter/:id/edit",
                name: "edit-data-filter",
                component: EditDataFilter,
                meta: {
                    authenticated: true,
                    company_specific: false
                },
                beforeEnter: (to, from) => {
                    if (['data-filters', 'company-data-filters'].includes(from.name)) {
                        localStorage.setItem('data-filter-back-route', from.name);
                    }
                    return true;
                },
            },
            {
                path: "data-filter/company-data-filters",
                name: "company-data-filters",
                component: CompanyDataFilters,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },

            {
                path: "role/roles",
                name: "roles",
                component: Roles,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "role/create",
                name: "create-role",
                component: CreateRole,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "role/:id/edit",
                name: "edit-role",
                component: EditRole,
                meta: {
                    authenticated: true,
                    company_specific: false
                }
            },

            {
                path: "order/orders",
                name: "orders",
                component: Orders,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
        ],
    },

    {
        path: "",
        component: LayoutSimple,
        children: [
            {
                path: "login",
                name: "login",
                component: Login,
                meta: {
                    authenticated: false
                }
            }
        ],

    },

    {
        path: "/:catchAll(.*)",
        name: "not_found",
        component: NotFound,
        meta: {
            authenticated: false,
            company_specific: false
        }
    }
];

// Create Router
const router = createRouter({
    history: createWebHistory(),
    linkActiveClass: "active",
    linkExactActiveClass: "",
    scrollBehavior() {
        return {left: 0, top: 0};
    },
    routes,
});

// NProgress
/*eslint-disable no-unused-vars*/
NProgress.configure({showSpinner: true});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated();
    if (to.meta.authenticated) {
        if (isAuthenticated) {
            next();
        } else {
            localStorage.setItem('expected_route', to.name);
            router.push({name: 'login'})
        }
    } else {
        if (isAuthenticated && to.name !== 'not_found') {
            router.push({name: 'home'})
        } else {
            next();
        }
    }
});

router.beforeResolve((to, from, next) => {
    if (to.name) {
        NProgress.start();
    }
    next();
});

router.afterEach(() => {
    NProgress.done();
});
/*eslint-enable no-unused-vars*/

export default router;
