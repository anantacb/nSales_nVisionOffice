<script setup>
import {onMounted, ref, watch} from 'vue';
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
import {useCompanyStore} from "@/stores/companyStore";
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
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy,
    resetRequest
} = useGridManagement();

setTableFields([
    {
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "company_user",
        title: "Initials",
        formatter: (data) => {
            return data.Initials;
        }
    },
    {
        name: "Email",
        title: "Email",
        sortField: "Email"
    },
    {
        name: "InsertTime",
        title: "Insert Time",
        sortField: "InsertTime"
    },
    {
        name: "UpdateTime",
        title: "Update Time",
        sortField: "UpdateTime"
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
        title: "Action"
    }
]);
setSearchColumns(['Name', 'Email']);

onMounted(async () => {
    await getCompanyUsers();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCompanyUsers();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCompanyUsers();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCompanyUsers();
}

async function getCompanyUsers() {
    let {data, pagination} = await User.getCompanyUsers(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

watch(() => companyStore.getSelectedCompany, async () => {
    resetRequest();
    await getCompanyUsers();
});

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
            <router-link :to="{name: 'edit-company-user', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
        </template>
    </DataGrid>
</template>
