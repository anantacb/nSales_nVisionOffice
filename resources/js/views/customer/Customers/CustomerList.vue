<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import Customer from "@/models/Company/Customer";
import useGridManagement from "@/composables/useGridManagement";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let tableData = ref([]);
let paginationData = ref(null);
let isLoading = ref(true);

const {
    tableFields,
    bodyHeight,
    request,
    setTableFields,
    resetRequest,
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy
} = useGridManagement()

setTableFields([
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
]);
setSearchColumns(['Account', 'Name', 'Email']);

onMounted(() => {
    setPageNo(1);
    getCustomers();
});

watch(() => companyStore.getSelectedCompany, () => {
    resetRequest();
    getCustomers();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCustomers();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCustomers();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
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
                :key="'details_'+props.data.Id"
                :routeTo="{ name: 'customer-details', params: {id: props.data.Id} }"
                actionType="details"
                content="Details"
            />

            <ActionButton
                :key="'delete_'+props.data.Id"
                actionType="delete"
                content="Delete"
                @delete="deleteCustomer(props.data, props.index)"
            />
        </template>
    </DataGrid>
</template>
