import {useAuthStore} from "@/stores/authStore";
import _ from "lodash";
import router from "@/router";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useNotificationStore} from "@/stores/notificationStore";

const {isModuleEnabled} = useCompanyInfos();

export default function useCheckAccess() {
    async function checkAccess(roles, module = null) {
        const notificationStore = useNotificationStore()
        if (!_.isEmpty(roles) && !hasRoleAccess(roles)) {
            await router.push({
                name: 'home'
            });
            notificationStore.showNotification("Access Denied.", "error");
        }

        if (module && !isModuleEnabled(module)) {
            await router.push({
                name: 'home'
            });
            notificationStore.showNotification("Module Not Enabled.", "error");
        }
    }

    function hasRoleAccess(roles) {
        const authStore = useAuthStore();
        const authRoles = authStore.getRoles;
        return roles.some(role => authRoles.includes(role));
    }

    return {hasRoleAccess, checkAccess};
}
