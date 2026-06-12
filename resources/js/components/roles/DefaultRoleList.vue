<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import DefaultRole from "@/models/Office/DefaultRole";
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
    setPerPage,
    setSortBy,
} = useGridManagement();

setTableFields([
    {name: "Name", title: "Name", sortField: "Name"},
    {name: "Type", title: "Type", sortField: "Type"},
    {
        name: "Description",
        title: "Description",
        formatter: (data) => {
            if (data && data.length > 40) {
                return data.substring(0, 40) + '...';
            }
            return data;
        },
    },
    {name: "Action", title: "Action"},
]);
setSearchColumns(['Name', 'Type']);

onMounted(async () => {
    await getDefaultRoles();
});

function changePerPage(perPage) {
    setPerPage(perPage);
    goToPage(1);
}

function goToPage(pageNo) {
    setPageNo(pageNo);
    getDefaultRoles();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getDefaultRoles();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getDefaultRoles();
}

async function getDefaultRoles() {
    isLoading.value = true;
    const {data, pagination} = await DefaultRole.getDefaultRoles(request.value);
    tableData.value = data;
    paginationData.value = pagination;
    isLoading.value = false;
}

function deleteDefaultRole(role, index) {
    Swal.fire({
        title: 'Delete this default role?',
        html: `Default role <code>${role.Name}</code> will no longer be copied into new companies. Type <code class="text-danger">Confirm</code> to proceed.`,
        input: 'text',
        inputAttributes: {autocapitalize: 'off'},
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => text === 'Confirm',
        allowOutsideClick: () => !Swal.isLoading(),
    }).then(async (result) => {
        if (result.isConfirmed) {
            const {message} = await DefaultRole.delete(role.Id);
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
        @perPageChange="changePerPage"
        @search="search"
        @sortBy="sortBy"
    >
        <template v-slot:body-Action="props">
            <router-link :to="{name: 'edit-default-role', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteDefaultRole(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
