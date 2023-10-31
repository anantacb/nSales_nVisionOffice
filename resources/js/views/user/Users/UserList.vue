<script setup>
import {onMounted, ref} from 'vue';
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
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
    await getUsers();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getUsers();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getUsers();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getUsers();
}

async function getUsers() {
    let {data, pagination} = await User.getUsers(request.value);
    tableData.value = data;
    paginationData.value = pagination;
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
            <router-link :to="{name: 'edit-user', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
        </template>
    </DataGrid>
</template>
