import {defineStore} from "pinia";
import User from "@/models/Office/User";
import {useCookies} from "vue3-cookies";
import {useCompanyStore} from "@/stores/companyStore";

const {cookies} = useCookies();

// Main Pinia Store
export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : {},
        roles: [],
        permissions: []
    }),

    actions: {
        async setToken(payload) {
            cookies.set("token", payload.access_token, payload.expires_in - 2);
            const token = cookies.get('token');
            axios.defaults.headers.common['Authorization'] = `${payload.token_type} ${token}`;
            await this.getAuthUserDetails();
        },

        setRoles(payload) {
            this.roles = payload;
        },

        setPermissions(payload) {
            this.permissions = payload ?? [];
        },

        logout() {
            cookies.remove("token");

            const darkMode = localStorage.getItem('darkMode');
            const darkModeSystem = localStorage.getItem('darkModeSystem');
            localStorage.clear();
            if (darkMode !== null) localStorage.setItem('darkMode', darkMode);
            if (darkModeSystem !== null) localStorage.setItem('darkModeSystem', darkModeSystem);

            delete axios.defaults.headers.common['Authorization'];
            this.clearStorage();
            const companyStore = useCompanyStore();
            companyStore.clearStorage();
        },

        async getAuthUserDetails() {
            let {data} = await User.getAuthUserDetails();
            this.user = data;
            localStorage.setItem('user', JSON.stringify(this.user));
        },

        getToken() {
            return cookies.get('token');
        },

        isAuthenticated() {
            return !!cookies.get('token');
        },

        clearStorage() {
            this.user = {};
            this.roles = [];
            this.permissions = [];
        }
    },

    getters: {
        getUser() {
            return this.user;
        },
        getRoles() {
            return this.roles;
        },
        getPermissions() {
            return this.permissions;
        }
    }
});
