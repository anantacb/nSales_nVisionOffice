import {defineStore} from "pinia";
import User from "@/models/Office/User";
import {useCookies} from "vue3-cookies";
import {useCompanyStore} from "@/stores/companyStore";

const {cookies} = useCookies();

// Main Pinia Store
export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : {},
        roles: []
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

        logout() {
            cookies.remove("token");
            localStorage.clear();
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
        }
    },

    getters: {
        getUser() {
            return this.user;
        },
        getRoles() {
            return this.roles;
        }
    }
});
