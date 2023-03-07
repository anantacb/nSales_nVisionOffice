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
        async setToken(payload) {
            this.token = payload.access_token;
            this.expires_in = payload.expires_in;
            localStorage.setItem('token', this.token);
            localStorage.setItem('expires_in', this.expires_in);
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            await this.getAuthUserDetails();
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

        async getAuthUserDetails() {
            try {
                let {data} = await User.getAuthUserDetails();
                this.user = data;
                localStorage.setItem('user', JSON.stringify(this.user));
            } catch (err) {
            }
        },

        getToken() {
            return this.token;
        },

        isAuthenticated() {
            /*let localDate = new Date();
            let utcDate = new Date(
                localDate.getUTCFullYear(),
                localDate.getUTCMonth(),
                localDate.getUTCDate(),
                localDate.getUTCHours(),
                localDate.getUTCMinutes(),
                localDate.getUTCSeconds()
            );
            let utcDateTime = Math.floor(utcDate.getTime() / 1000);*/
            // Expire at is coming in UTC

            // Check for expire at

            return !!this.token;
        },
    },

    getters: {
        /*isAuthenticated() {
            console.log('isAuthenticated');
            let localDate = new Date();
            let utcDate = new Date(
                localDate.getUTCFullYear(),
                localDate.getUTCMonth(),
                localDate.getUTCDate(),
                localDate.getUTCHours(),
                localDate.getUTCMinutes(),
                localDate.getUTCSeconds()
            );
            let utcDateTime = Math.floor(utcDate.getTime() / 1000); // Expire at is coming in UTC

            // Check for expire at
            if (utcDateTime > parseInt(this.expire_at)) {
                console.log(utcDateTime, parseInt(this.expire_at));
                return false;
            }
            return !!this.token;
        },*/

        getUser() {
            return this.user;
        },
    }
});
