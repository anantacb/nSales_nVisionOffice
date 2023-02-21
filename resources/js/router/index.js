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

const CreateSetting = () => import("@/views/setting/CreateSetting.vue");
const UpdateSetting = () => import("@/views/setting/UpdateSetting.vue");

const Modules = () => import("@/views/module/Modules.vue");
const CreateModule = () => import("@/views/module/CreateModule.vue");
const ActivateModule = () => import("@/views/module/ActivateModule.vue");

const Companies = () => import("@/views/company/Companies.vue");
const CreateCompany = () => import("@/views/company/CreateCompany.vue");

const Users = () => import("@/views/user/Users.vue");
const CreateCompanyUser = () => import("@/views/user/CreateCompanyUser.vue");

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
                path: "setting/update",
                name: "update-setting",
                component: UpdateSetting,
                meta: {
                    authenticated: true,
                    company_specific: true
                }
            },
            {
                path: "setting/create",
                name: "create-setting",
                component: CreateSetting,
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
                path: "module/activate",
                name: "activate-module",
                component: ActivateModule,
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
                path: "user/users",
                name: "users",
                component: Users,
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
        component: Login,
        meta: {
            requiresAuth: false
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
        if (isAuthenticated) {
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
