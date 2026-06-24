import {useAuthStore} from "@/stores/authStore";
import _ from "lodash";
import router from "@/router";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useNotificationStore} from "@/stores/notificationStore";

const {isModuleEnabled} = useCompanyInfos();

export default function useCheckAccess() {
    async function checkAccess(roles, module = null, permissions = null) {
        const notificationStore = useNotificationStore()

        if (module && !isModuleEnabled(module)) {
            await router.push({
                name: 'home'
            });
            notificationStore.showNotification(`${module} Module Not Enabled.`, "error");
            return;
        }

        if (!hasAccess(roles, permissions)) {
            await router.push({
                name: 'home'
            });
            notificationStore.showNotification("Access Denied.", "error");
        }
    }

    function hasRoleAccess(roles) {
        const authStore = useAuthStore();
        const authRoles = authStore.getRoles ?? [];
        return (roles ?? []).some(role => authRoles.includes(role));
    }

    // Access is granted when the user matches ANY of the defined roles OR ANY of the
    // defined permissions. Empty/undefined role and permission lists are ignored, so a
    // node with neither constraint is open to everyone (headings, generic links).
    function hasAccess(roles, permissions) {
        roles = roles ?? [];
        permissions = permissions ?? [];
        const hasRoles = !_.isEmpty(roles);
        const hasPerms = !_.isEmpty(permissions);
        if (!hasRoles && !hasPerms) return true;
        let granted = false;
        if (hasRoles) granted = granted || hasRoleAccess(roles);
        if (hasPerms) granted = granted || hasAnyPermission(permissions);
        return granted;
    }

    // Menu-node access: the module (if module-specific) must be enabled, then the
    // role-or-permission rule above decides visibility.
    function canAccessNode(node) {
        if (node.moduleSpecific && !isModuleEnabled(node.moduleName)) return false;
        return hasAccess(node.roles, node.permissions);
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

    return {hasRoleAccess, checkAccess, isBypassRole, hasPermission, hasAnyPermission, hasAccess, canAccessNode};
}
