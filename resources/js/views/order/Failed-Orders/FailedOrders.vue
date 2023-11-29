<script setup>
import FailedOrderList from "@/views/order/Failed-Orders/FailedOrderList.vue";
import {useCompanyStore} from "@/stores/companyStore";
import {ref} from "vue";

const companyStore = useCompanyStore();
let totalOrderCount = ref(0);

function totalOrderCounter(totalCount) {
    totalOrderCount.value = totalCount ?? totalOrderCount.value;
}

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Failed Orders (${totalOrderCount})`">
            <FailedOrderList @totalOrderCounter="totalOrderCounter"/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
