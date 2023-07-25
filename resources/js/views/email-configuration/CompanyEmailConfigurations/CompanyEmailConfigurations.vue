<script setup>

import CompanyEmailConfigurationList
    from "@/views/email-configuration/CompanyEmailConfigurations/CompanyEmailConfigurationList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>

    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Email Configurations (${companyStore.selectedCompany.Name})`">
            <CompanyEmailConfigurationList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
