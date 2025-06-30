<script setup>
import {onMounted, ref} from "vue";
import Order from "@/models/Company/Order";
import {useAuthStore} from "@/stores/authStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useRoute} from "vue-router";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();

let salesYTDRef = ref(null);
let salesMTDRef = ref(null);
let orderYTDRef = ref(null);
let orderMTDRef = ref(null);
let salesInfo = ref({});
let orderInfo = ref({});

const props = defineProps({
    itemId: {
        type: String,
        required: true,
    },
});

async function getSalesYtd() {
    salesYTDRef.value.statusLoading();
    let {data} = await Order.fetchSalesYearlyByItem(companyStore.selectedCompany.Id, props.itemId);
    salesInfo.value.currentYear = data.currentYearSales;
    salesInfo.value.lastYear = data.lastYearSales;
    // console.log(salesInfo);
    salesYTDRef.value.statusNormal();
}

async function getSalesMtd() {
    salesMTDRef.value.statusLoading();
    let {data} = await Order.fetchSalesMonthlyByItem(companyStore.selectedCompany.Id, props.itemId);
    salesInfo.value.currentYearCurrentMonth = data.currentYearCurrentMonthSales;
    salesInfo.value.lastYearCurrentMonth = data.lastYearCurrentMonthSales;
    // console.log(salesInfo);
    salesMTDRef.value.statusNormal();
}

async function getOrdersYtd() {
    orderYTDRef.value.statusLoading();
    let {data} = await Order.fetchOrdersYearlyByItem(companyStore.selectedCompany.Id, props.itemId);
    orderInfo.value.currentYear = data.currentYearOrders;
    orderInfo.value.lastYear = data.lastYearOrders;
    // console.log(orderInfo);
    orderYTDRef.value.statusNormal();
}

async function getOrdersMtd() {
    orderMTDRef.value.statusLoading();
    let {data} = await Order.fetchOrdersMonthlyByItem(companyStore.selectedCompany.Id, props.itemId);
    orderInfo.value.currentYearCurrentMonth = data.currentYearCurrentMonthOrders;
    orderInfo.value.lastYearCurrentMonth = data.lastYearCurrentMonthOrders;
    // console.log(orderInfo);
    orderMTDRef.value.statusNormal();
}

onMounted(async () => {
    await getSalesYtd();
    await getSalesMtd();
    await getOrdersYtd();
    await getOrdersMtd();
});

</script>

<template>
    <!-- Overview -->
    <div class="row items-push">
        <!-- Sales YTD -->
        <div class="col-sm-6 col-xxl-3">
            <BaseBlock ref="salesYTDRef" class="d-flex flex-column h-100 mb-0">
                <template #content>
                    <div
                        class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center"
                    >
                        <dl class="mb-0">
                            <dt class="fs-3 fw-bold">
                                <template v-if="salesInfo.currentYear">
                                    {{ salesInfo.currentYear.Total }}
                                    {{ salesInfo.currentYear.Currency }}
                                </template>
                                <template v-else>No sales</template>
                            </dt>
                            <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                Last Year:
                                <template v-if="salesInfo.lastYear">
                                    {{ salesInfo.lastYear.Total }}
                                    {{ salesInfo.lastYear.Currency }}
                                </template>
                                <template v-else>No sales</template>
                            </dd>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-gifts fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <span
                            class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex text-primary">
                        SALES YTD
                      </span>
                    </div>
                </template>
            </BaseBlock>
        </div>
        <!-- END Sales YTD -->

        <!-- Sales MTD -->
        <div class="col-sm-6 col-xxl-3">
            <BaseBlock ref="salesMTDRef" class="d-flex flex-column h-100 mb-0">
                <template #content>
                    <div
                        class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center"
                    >
                        <dl class="mb-0">
                            <dt class="fs-3 fw-bold">
                                <template v-if="salesInfo.currentYearCurrentMonth">
                                    {{ salesInfo.currentYearCurrentMonth.Total }}
                                    {{ salesInfo.currentYearCurrentMonth.Currency }}
                                </template>
                                <template v-else>No sales</template>
                            </dt>
                            <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                Last Year, this month:
                                <template v-if="salesInfo.lastYearCurrentMonth">
                                    {{ salesInfo.lastYearCurrentMonth.Total }}
                                    {{ salesInfo.lastYearCurrentMonth.Currency }}
                                </template>
                                <template v-else>No sales</template>
                            </dd>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-gift fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <span
                            class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex text-primary">
                            SALES MTD
                        </span>
                    </div>
                </template>
            </BaseBlock>
        </div>
        <!-- END Sales MTD -->

        <!-- PCS YTD -->
        <div class="col-sm-6 col-xxl-3">
            <BaseBlock ref="orderYTDRef" class="d-flex flex-column h-100 mb-0">
                <template #content>
                    <div
                        class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center"
                    >
                        <dl class="mb-0">
                            <dt class="fs-3 fw-bold">
                                <template v-if="orderInfo.currentYear">
                                    {{ orderInfo.currentYear.Total }} PCS
                                </template>
                            </dt>
                            <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                Last Year:
                                <template v-if="orderInfo.lastYear">
                                    {{ orderInfo.lastYear.Total }} PCS
                                </template>
                            </dd>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-cart-arrow-down fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <span
                            class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex text-primary">
                        PCS YTD
                    </span>
                    </div>
                </template>
            </BaseBlock>
        </div>
        <!-- END PCS YTD -->

        <!-- PCS MTD -->
        <div class="col-sm-6 col-xxl-3">
            <BaseBlock ref="orderMTDRef" class="d-flex flex-column h-100 mb-0">
                <template #content>
                    <div
                        class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center"
                    >
                        <dl class="mb-0">
                            <dt class="fs-3 fw-bold">
                                <template v-if="orderInfo.currentYearCurrentMonth">
                                    {{ orderInfo.currentYearCurrentMonth.Total }} PCS
                                </template>
                            </dt>
                            <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                Last Year, this month:
                                <template v-if="orderInfo.lastYearCurrentMonth">
                                    {{ orderInfo.lastYearCurrentMonth.Total }} PCS
                                </template>
                            </dd>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-cart-shopping fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <span
                            class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex text-primary">
                            PCS MTD
                        </span>
                    </div>
                </template>
            </BaseBlock>
        </div>
        <!-- END PCS MTD -->
    </div>
    <!-- END Overview -->
</template>

<style scoped>
</style>
