<script setup>
import CustomerVisitList from "@/views/customer-visit/CustomerVisits/CustomerVisitList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Customer Visits (${companyStore.selectedCompany.Name})`">
            <CustomerVisitList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
