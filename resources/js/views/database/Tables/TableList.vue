<script setup>
import {onMounted, ref} from 'vue';
import Table from "@/models/Office/Table";
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();

let tableData = ref([]);
let tableFields = [
    {
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "module",
        title: "Module",
        formatter: (module) => {
            return module ? module.Name : '';
        }
    },
    {
        name: "Version",
        title: "Version",
        sortField: "Version"
    },
    {
        name: "ClientSync",
        title: "Client Sync",
        sortField: "ClientSync"
    },
    {
        name: "company_tables",
        title: "Company",
        formatter: (company_tables) => {
            let company_batches = ``;
            company_tables.forEach((company_table) => {
                company_batches += `<span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">${company_table.company.Name}</span>`
            });
            return company_batches;
        }
    },
    {
        name: "Disabled",
        title: "Disabled",
        formatter: (data) => {
            return data ? "Yes" : "No";
        }
    },
    {
        name: "Action",
        title: "Action",
        sortField: ""
    }
];
let bodyHeight = "100vh";
let paginationData = ref(null);
let isLoading = ref(true);
let request = ref({
    search_columns: ['Name'],
    //relations: [],
    filters: null,
    order: {},
    pagination: {"page_no": 1, "per_page": 20},
    query: null
});

onMounted(() => {
    getTables();
});

function goToPage(pageNo) {
    request.value.pagination.page_no = pageNo;
    getTables();
}

function sortBy({field, order}) {
    request.value.order = [
        {"column": field, "sort": order}
    ];
    request.value.pagination.page_no = 1;
    getTables();
}

function search(query) {
    request.value.query = query;
    request.value.pagination.page_no = 1;
    getTables();
}

async function getTables() {
    let {data, pagination} = await Table.getTables(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteTable(table, index) {
    Swal.fire({
        title: 'Are you sure? Delete table?',
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
            let {data, message} = await Table.delete(table.Id);
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
            <PopOverButton
                btnClass="btn rounded-pill btn-alt-primary me-1"
                content="Manage Table Fields"
                iconClass="fa fa-table-cells"
                @click="$router.push({name: 'manage-table-fields', params: {id: props.data.Id}})"
            ></PopOverButton>
            <PopOverButton
                btnClass="btn rounded-pill btn-alt-info me-1"
                content="Manage Table Indices"
                iconClass="fa fa-table-cells-large"
                @click="$router.push({name: 'manage-table-fields', params: {id: props.data.Id}})"
            ></PopOverButton>
            <router-link :to="{name: 'edit-table', params: {id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteTable(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
