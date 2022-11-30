<script setup>
import {onMounted, ref} from 'vue';
import DataGrid from "@/components/ui/DataGrid/DataGrid.vue";
import Table from "@/models/Table";

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
    order: null,
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

function getTables() {
    Table.getTables(request.value)
        .then(({data}) => {
            tableData.value = data.data;
            paginationData.value = data.pagination;
        })
        .catch(err => {
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
    ></DataGrid>
</template>
