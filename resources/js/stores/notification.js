import {defineStore} from "pinia";
import {ref} from "vue";

export const useNotificationStore = defineStore({
    id: 'notification',

    state: () => ({
        notifications: ref([]),
        count: 0
    }),
    actions: {
        showNotification(message, type = 'success', timer = 5000) {
            this.notifications.push({
                message: message,
                timer: timer,
                type: type
            });
            this.count++
            setTimeout(() => {
                this.notifications.splice(this.count - 1, 1);
                this.count--;
            }, timer + 1000);
        }
    },
    getters: {}
});
