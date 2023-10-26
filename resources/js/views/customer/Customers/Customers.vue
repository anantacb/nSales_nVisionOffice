<script setup>
import CustomerList from "@/views/customer/Customers/CustomerList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>
    <!-- Hero -->
    <!--    <BasePageHeading subtitle="" title="Dashboard">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        Dashboard
                    </li>
                </ol>
            </nav>
        </template>
    </BasePageHeading>-->
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Customers (${companyStore.selectedCompany.Name})`">
            <CustomerList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
