<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import Customer from "@/models/Company/Customer";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let tableData = ref([]);
let tableFields = [
    {
        name: "Account",
        title: "Account",
    },
    {
        name: "Name",
        title: "Name",
    },
    {
        name: "Address",
        title: "Address",
    },
    {
        name: "Zipcode",
        title: "Zipcode"
    },
    {
        name: "City",
        title: "City"
    },
    {
        name: "Country",
        title: "Country"
    },
    {
        name: "Email",
        title: "Email"
    },
    {
        name: "Phone",
        title: "Phone"
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
    search_columns: ['Account', 'Name', 'Email'],
    //relations: [],
    filters: null,
    order: {},
    pagination: {"page_no": 1, "per_page": 20},
    query: null
});

onMounted(() => {
    request.value.pagination.page_no = 1;
    getCustomers();
});

watch(() => companyStore.getSelectedCompany, () => {
    getCustomers();
});

function goToPage(pageNo) {
    request.value.pagination.page_no = pageNo;
    getCustomers();
}

function sortBy({field, order}) {
    request.value.order = [
        {"column": field, "sort": order}
    ];
    request.value.pagination.page_no = 1;
    getCustomers();
}

function search(query) {
    request.value.query = query;
    request.value.pagination.page_no = 1;
    getCustomers();
}

async function getCustomers() {
    let {data, pagination} = await Customer.getCustomers(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCustomer(customer, index) {
    Swal.fire({
        title: 'Are you sure? Delete Customer?',
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
            let {data, message} = await Customer.delete(companyStore.selectedCompany.Id, customer.Id);
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
            <!--            <router-link :to="{name: 'customer-details', params:{id: props.data.Id}}"
                                     class="btn rounded-pill btn-alt-warning me-1">
                            <i class="fa fa-pen-alt"></i>
                        </router-link>-->
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteCustomer(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
