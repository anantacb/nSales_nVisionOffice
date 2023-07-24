<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Order from "@/models/Company/Order";
import {useCompanyStore} from "@/stores/companyStore";
import moment from "moment";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let tableData = ref([]);
let tableFields = [
    {
        name: "OrderNumber",
        title: "Order Number",
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
        title: "Total"
    },
    {
        name: "Employee",
        title: "Rep"
    },
    {
        name: "OrderDate",
        title: "Date",
        formatter: (data) => {
            return moment(data).format('YYYY-MM-DD');
        }
    },
    {
        name: "DeliveryDate",
        title: "Delivery",
        formatter: (data) => {
            return moment(data).format('YYYY-MM-DD');
        }
    },
    {
        name: "ExportStatus",
        title: "Status"
    },
    {
        name: "Action",
        title: "Action"
    }
];
let bodyHeight = "100vh";
let paginationData = ref(null);
let isLoading = ref(true);
let request = ref({
    search_columns: ['Name', 'CustomerAccount', 'OrderNumber'],
    //relations: [],
    filters: null,
    order: {},
    pagination: {"page_no": 1, "per_page": 20},
    query: null
});

onMounted(() => {
    request.value.pagination.page_no = 1;
    getOrders();
});

watch(() => companyStore.getSelectedCompany, () => {
    getOrders();
});

function goToPage(pageNo) {
    request.value.pagination.page_no = pageNo;
    getOrders();
}

function sortBy({field, order}) {
    request.value.order = [
        {"column": field, "sort": order}
    ];
    request.value.pagination.page_no = 1;
    getOrders();
}

function search(query) {
    request.value.query = query;
    request.value.pagination.page_no = 1;
    getOrders();
}

async function getOrders() {
    let {data, pagination} = await Order.getOrders(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
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
        }
    });
}

</script>

<template>
    <DataGrid
        :expandable="false"
        :height="`${bodyHeight - 115}px`"
        :isLoading="isLoading"
        :pagination="paginationData"
        :searchable="true"
        :tabledata="tableData"
        :tablefields="tableFields"
        @expand=""
        @paginate="goToPage"
        @search="search"
        @sortBy="sortBy"
    >
        <template v-slot:body-Action="props">
            <!--            <router-link :to="{name: 'order-details', params:{id: props.data.Id}}"
                                     class="btn rounded-pill btn-alt-warning me-1">
                            <i class="fa fa-pen-alt"></i>
                        </router-link>-->
            <!--            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                                @click="deleteOrder(props.data, props.index)">
                            <i class="fa fa-trash-alt"></i>
                        </button>-->
        </template>
    </DataGrid>
</template>
