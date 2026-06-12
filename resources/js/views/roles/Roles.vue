<script setup>
import RoleList from "@/components/roles/RoleList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Company Roles (${companyStore.selectedCompany.Name})`">
            <template #options>
                <router-link :to="{name:'create-role'}" class="btn btn-sm btn-outline-primary">
                    <i class="far fa-fw fa-plus"></i> Create Company Role
                </router-link>
            </template>
            <RoleList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
