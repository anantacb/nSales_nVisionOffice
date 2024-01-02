<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Order from "@/models/Company/Order";
import {useCompanyStore} from "@/stores/companyStore";
import useGridManagement from "@/composables/useGridManagement";
import {useFormatter} from "@/composables/useFormatter";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
const emit = defineEmits(['totalOrderCounter']);

let tableData = ref([]);
let paginationData = ref(null);
let isLoading = ref(true);
let orderOriginsOptions = ref();
let {numberFormat, dateFormat} = useFormatter();
let dateFormatStr = ref('DD-MM-YYYY');
let exportStatusOptions = ref({
    'Exported': 'Exported',
    'NotExported': 'Waiting for Export',
});
let generalSortingOptions = ref({
    'NewestFirst': 'Newest First',
    'OldestFirst': 'Oldest First',
    'OrderTotalHighest': 'Order Total Highest',
    'OrderTotalLowest': 'Order Total Lowest',
});

const {
    tableFields,
    bodyHeight,
    request,
    setTableFields,
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy,
    resetRequest
} = useGridManagement();

setTableFields([
    {
        name: "CalculatedOrderDate",
        title: "Date",
        formatter: (data) => {
            return dateFormat(data, dateFormatStr.value);
        },
        // sortField: "CalculatedOrderDate"
    },
    {
        name: "CalculatedOrderOrigin",
        title: "Origin",
    },
    {
        name: "Type",
        title: "Type",
    },
    {
        name: "OrderNumber",
        title: "Order No.",
    },
    {
        name: "CustomerAccount",
        title: "Account",
    },
    {
        name: "Name",
        title: "Name",
    },

    {
        name: "TotalIncVat",
        title: "Total",
        formatter: (data) => {
            return numberFormat(data);
        }
    },
    {
        name: "Employee",
        title: "Employee"
    },
    {
        name: "ExportStatus",
        title: "Status"
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Name', 'CustomerAccount', 'OrderNumber']);

onMounted(() => {
    setPageNo(1);
    getOrders();
    getOrderOrigins();
    resetFilters();
});

watch(() => companyStore.getSelectedCompany, () => {
    resetRequest();
    resetFilters();
    //getOrderOrigins();
    getOrders();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getOrders();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getOrders();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getOrders();
}

function resetFilters() {
    request.value.filters = {OrderOrigin: '', ExportStatus: '', general: ''};
}

function setFilters(filters) {
    request.value.filters = filters;
}

async function getOrders() {
    let {data, pagination} = await Order.getOrders(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
    emit('totalOrderCounter', paginationData.value.total);
}

async function getOrderOrigins() {
    let {data} = await Order.getOrderOrigins(companyStore.selectedCompany.Id);
    orderOriginsOptions.value = data.orderOriginsOptions;
}

function filtersOptionChange() {
    setPageNo(1);
    getOrders();
}

function deleteOrder(module, index) {
    Swal.fire({
        title: 'Are you sure? Delete Order?',
        html: 'Please type <code class="text-danger">Confirm</code> and press delete.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            let {data, message} = await Order.delete(module.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
            emit('totalOrderCounter', --paginationData.value.total);
        }
    });
}

</script>

<template>

    <!-- Filters Block -->
    <div class="form-group filter-group">
        <div class="row">
            <div class="col-3">
                <select v-model="request.filters.OrderOrigin"
                        class="form-control fs-sm"
                        @change="filtersOptionChange">
                    <option value="">Origin</option>
                    <option v-for="(orderOriginLabel, orderOriginValue) of orderOriginsOptions"
                            :value="orderOriginValue"> {{ orderOriginLabel }}
                    </option>
                    <option value="">Reset</option>
                </select>
            </div>
            <div class="col-3">
                <select v-model="request.filters.ExportStatus"
                        class="form-control fs-sm"
                        @change="filtersOptionChange">
                    <option value="">Export Status</option>
                    <option v-for="(exportStatusLabel, exportStatusValue) in exportStatusOptions"
                            :value="exportStatusValue"> {{ exportStatusLabel }}
                    </option>
                    <option value="">Reset</option>
                </select>
            </div>

            <div class="col-3">
                <select v-model="request.filters.general"
                        class="form-control fs-sm"
                        @change="filtersOptionChange">
                    <option value="">General Sorting</option>
                    <option v-for="(generalSortingLabel, generalSortingValue) in generalSortingOptions"
                            :value="generalSortingValue"> {{ generalSortingLabel }}
                    </option>
                    <option value="">Reset</option>
                </select>
            </div>
        </div>
    </div>
    <!-- Filters Block -->

    <DataGrid
        :expandable="false"
        :height="bodyHeight"
        :isLoading="isLoading"
        :pagination="paginationData"
        :searchString="request.query"
        :searchable="true"
        :tableData="tableData"
        :tableFields="tableFields"
        @expand=""
        @paginate="goToPage"
        @search="search"
        @sortBy="sortBy"
    >
        <template v-slot:body-Action="props">
            <ActionButton
                :key="'details_'+props.data.UUID"
                :routeTo="{ name: 'order-details', params: {id: props.data.UUID} }"
                actionType="details"
                content="Details"
            />

            <!--            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                                @click="deleteOrder(props.data, props.index)">
                            <i class="fa fa-trash-alt"></i>
                        </button>-->
        </template>
    </DataGrid>
</template>

<style scoped>
@media only screen and (min-width: 768px) {
    .filter-group {
        bottom: -42px;
        margin-top: -35px;
        position: relative;
        width: 70%;
        z-index: 999;
    }
}
</style>
