import {defineStore} from "pinia";
import User from "@/models/Office/User";

// Main Pinia Store
export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token'),
        expire_at: localStorage.getItem('expire_at'),
        user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : {}
    }),

    actions: {
        setToken(payload) {
            this.token = payload.access_token;
            this.expire_at = payload.expire_at;
            localStorage.setItem('token', this.token);
            localStorage.setItem('expire_at', this.expire_at);
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            this.getUserDetails();
        },

        logout() {
            localStorage.clear();
            /*localStorage.removeItem('token');
            localStorage.removeItem('expire_at');
            localStorage.removeItem('user');*/
            this.token = null;
            this.expire_at = null
            this.user = {};
            delete axios.defaults.headers.common['Authorization'];
        },

        async getUserDetails() {
            try {
                let {data} = await User.getDetails();
                this.user = data.data;
                localStorage.setItem('user', JSON.stringify(this.user));
            } catch (err) {
            }
        },

        getToken() {
            return this.token;
        }
    },

    getters: {
        isAuthenticated() {
            return !!this.token;
        },

        getUser() {
            return this.user;
        },
    }
});
