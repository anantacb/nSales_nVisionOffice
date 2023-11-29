<script setup>
import OrderList from "@/views/order/Orders/OrderList.vue";
import {useCompanyStore} from "@/stores/companyStore";
import {ref} from "vue";

const companyStore = useCompanyStore();
let totalOrderCount =ref(0);

function totalOrderCounter(totalCount) {
    totalOrderCount.value = totalCount ?? totalOrderCount.value;
}
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
        <BaseBlock :title="`Orders (${totalOrderCount})`">
            <OrderList @totalOrderCounter="totalOrderCounter"/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
