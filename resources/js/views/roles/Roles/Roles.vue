<script setup>
import RoleList from "@/views/roles/Roles/RoleList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Roles (${companyStore.selectedCompany.Name})`">
            <RoleList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
