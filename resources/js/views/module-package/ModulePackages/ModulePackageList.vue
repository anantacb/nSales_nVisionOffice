<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";

import useGridManagement from "@/composables/useGridManagement";
import ModulePackage from "@/models/Office/ModulePackage";

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
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(["Name", "Type"]);

onMounted(() => {
    getModulePackages();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getModulePackages();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getModulePackages();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getModulePackages();
}

async function getModulePackages() {
    let {data, pagination} = await ModulePackage.getModulePackages(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteModulePackage(modulePackage, index) {
    Swal.fire({
        title: 'Are you sure? Delete ModulePackage?',
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
            let {data, message} = await ModulePackage.delete(modulePackage.Id);
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
            <router-link :to="{name: 'edit-module-package', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteModulePackage(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
