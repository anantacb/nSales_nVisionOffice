<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Application from "@/models/Office/Application";
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
        name: "Platform",
        title: "Platform",
        sortField: "Platform"
    },
    {
        name: "OperatingSystem",
        title: "Operating System",
        sortField: "OperatingSystem"
    },
    {
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(["Name", "Platform", "OperatingSystem"]);

onMounted(() => {
    getApplications();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getApplications();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getApplications();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getApplications();
}

async function getApplications() {
    let {data, pagination} = await Application.getApplications(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteApplication(application, index) {
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
            let {data, message} = await Application.delete(application.Id);
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
            <router-link :to="{name: 'edit-application', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteApplication(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
