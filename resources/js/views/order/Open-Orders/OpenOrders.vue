<script setup>
import OpenOrderList from "@/views/order/Open-Orders/OpenOrderList.vue";
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
        <BaseBlock :title="`Open Orders (${totalOrderCount})`">
            <OpenOrderList @totalOrderCounter="totalOrderCounter"/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
