import {createRouter, createWebHistory} from "vue-router";
import NProgress from "nprogress/nprogress.js";

import LayoutBackend from "@/layouts/variations/Backend.vue";

const Home = () => import("@/views/Home.vue");
const Tables = () => import("@/views/database/Tables.vue");

const routes = [
    {
        path: "/",
        component: LayoutBackend,
        children: [
            {
                path: "",
                name: "home",
                component: Home,
            },
            {
                path: "tables",
                name: "tables",
                component: Tables,
            },
        ],
    },
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
NProgress.configure({showSpinner: false});

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
