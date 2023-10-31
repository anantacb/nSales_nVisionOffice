<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Company from "@/models/Office/Company";
import useGridManagement from "@/composables/useGridManagement";

const notificationStore = useNotificationStore();

let tableData = ref([]);
let paginationData = ref(null);
let isLoading = ref(true);

const {
    tableFields,
    bodyHeight,
    request,
    setTableFields,
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy
} = useGridManagement();

setTableFields([
    {
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "DomainName",
        title: "DomainName",
        sortField: "DomainName"
    },
    {
        name: "IntegrationType",
        title: "IntegrationType",
        sortField: "IntegrationType"
    },
    {
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "ZipCode",
        title: "ZipCode",
        sortField: "ZipCode"
    },
    {
        name: "City",
        title: "City",
        sortField: "City"
    },
    {
        name: "Country",
        title: "Country",
        sortField: "Country"
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Name', 'DomainName', 'DatabaseName', 'CompanyName']);

onMounted(() => {
    getCompanies();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCompanies();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCompanies();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCompanies();
}

async function getCompanies() {
    let {data, pagination} = await Company.getCompanies(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCompany(company, index) {
    Swal.fire({
        title: 'Are you sure? Delete Company?',
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
            let {data, message} = await Company.delete(company.Id);
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
            <router-link :to="{name: 'edit-company', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteCompany(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
