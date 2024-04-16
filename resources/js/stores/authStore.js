import {defineStore} from "pinia";
import User from "@/models/Office/User";
import {useCookies} from "vue3-cookies";

const {cookies} = useCookies();

// Main Pinia Store
export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : {}
    }),

    actions: {
        async setToken(payload) {
            cookies.set("token", payload.access_token, payload.expires_in - 2);
            const token = cookies.get('token');
            axios.defaults.headers.common['Authorization'] = `${payload.token_type} ${token}`;
            await this.getAuthUserDetails();
        },

        logout() {
            cookies.remove("token");
            localStorage.clear();
            delete axios.defaults.headers.common['Authorization'];
            this.user = {};
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
    },

    getters: {
        getUser() {
            return this.user;
        },
    }
});
