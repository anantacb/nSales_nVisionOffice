<script setup>
import {ref} from "vue";
import Swal from "sweetalert2";
import DefaultRoleList from "@/components/roles/DefaultRoleList.vue";
import DefaultRole from "@/models/Office/DefaultRole";
import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();
const isSyncing = ref(false);

async function syncAllToCompanyRoles() {
    const {isConfirmed} = await Swal.fire({
        title: 'Sync all templates to company roles?',
        html: 'This pushes every default role template\'s <strong>saved</strong> permissions to all '
            + 'matching company roles, across all companies. Existing grants are kept (additive).',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sync All',
        confirmButtonColor: '#0d6efd',
    });
    if (!isConfirmed) return;

    isSyncing.value = true;
    try {
        const {message} = await DefaultRole.syncAllPermissionsToCompanyRoles();
        notificationStore.showNotification(message);
    } catch (error) {
        notificationStore.showNotification(
            error.response?.data?.message ?? 'Failed to sync permissions.',
            'error',
        );
    } finally {
        isSyncing.value = false;
    }
}
</script>

<template>
    <div class="content">
        <BaseBlock title="Default Roles">
            <template #options>
                <button :disabled="isSyncing" class="btn btn-sm btn-outline-success me-1" type="button"
                        @click="syncAllToCompanyRoles">
                    <i class="fa fa-fw fa-sync"></i> Sync All to Company Roles
                </button>
                <router-link :to="{name:'create-default-role'}" class="btn btn-sm btn-outline-primary">
                    <i class="far fa-fw fa-plus"></i> Create Default Role
                </router-link>
            </template>
            <p class="text-muted small mb-3">
                Default roles (rows with no <code>CompanyId</code>) are the templates copied into every newly created
                company. Editing here affects all <em>future</em> company-creations; existing company roles are not
                changed.
            </p>
            <DefaultRoleList/>
        </BaseBlock>
    </div>
</template>
