<script setup>
import PermissionList from "@/components/permission/PermissionList.vue";
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Permissions">
            <template #options>
                <router-link :to="{name:'create-permission'}" class="btn btn-sm btn-outline-primary">
                    <i class="far fa-fw fa-plus"></i> Create Permission
                </router-link>
            </template>
            <PermissionList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
