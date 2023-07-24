<script setup>
import OrderList from "@/views/order/Orders/OrderList.vue";
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
        <BaseBlock :title="`Orders (${companyStore.selectedCompany.Name})`">
            <OrderList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
