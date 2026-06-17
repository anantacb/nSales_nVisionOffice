import {useAuthStore} from "@/stores/authStore";
import _ from "lodash";
import router from "@/router";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useNotificationStore} from "@/stores/notificationStore";

const {isModuleEnabled} = useCompanyInfos();

export default function useCheckAccess() {
    async function checkAccess(roles, module = null, permissions = null) {
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
            notificationStore.showNotification(`${module} Module Not Enabled.`, "error");
        }

        if (!_.isEmpty(permissions) && !hasAnyPermission(permissions)) {
            await router.push({
                name: 'home'
            });
            notificationStore.showNotification("Access Denied.", "error");
        }
    }

    function hasRoleAccess(roles) {
        const authStore = useAuthStore();
        const authRoles = authStore.getRoles;
        return roles.some(role => authRoles.includes(role));
    }

    // Developer / Administrator implicitly pass every permission gate.
    function isBypassRole() {
        const authStore = useAuthStore();
        const authRoles = authStore.getRoles ?? [];
        return authRoles.includes('Developer') || authRoles.includes('Administrator');
    }

    function hasPermission(slug) {
        if (isBypassRole()) return true;
        const authStore = useAuthStore();
        return (authStore.getPermissions ?? []).includes(slug);
    }

    function hasAnyPermission(slugs) {
        if (isBypassRole()) return true;
        return slugs.some(slug => hasPermission(slug));
    }

    return {hasRoleAccess, checkAccess, isBypassRole, hasPermission, hasAnyPermission};
}
