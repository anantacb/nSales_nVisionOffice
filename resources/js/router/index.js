import {createRouter, createWebHistory} from "vue-router";
import NProgress from "nprogress/nprogress.js";

import LayoutBackend from "@/layouts/variations/Backend.vue";
import LayoutSimple from "@/layouts/variations/Simple.vue";
import {useAuthStore} from "@/stores/auth";

const Home = () => import("@/views/Home.vue");
const Tables = () => import("@/views/database/Tables/Tables.vue");
const Login = () => import("@/views/auth/Login.vue");

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
                    authenticated: true
                }
            },
            {
                path: "tables",
                name: "tables",
                component: Tables,
                meta: {
                    authenticated: true
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
    const isAuthenticated = authStore.isAuthenticated;
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
