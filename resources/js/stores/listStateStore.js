import {defineStore} from "pinia";

export const useListStateStore = defineStore('listState', {
    state: () => ({
        rememberedRouteName: null,
        snapshot: null
    }),
    actions: {
        save(routeName, request) {
            if (!routeName) return;
            this.rememberedRouteName = routeName;
            this.snapshot = JSON.parse(JSON.stringify(request));
        },
        consume(routeName) {
            if (!routeName || this.rememberedRouteName !== routeName) {
                this.clear();
                return null;
            }
            return JSON.parse(JSON.stringify(this.snapshot));
        },
        clear() {
            this.rememberedRouteName = null;
            this.snapshot = null;
        }
    }
});
