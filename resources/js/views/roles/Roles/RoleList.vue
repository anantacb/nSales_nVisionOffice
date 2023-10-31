<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Role from "@/models/Office/Role";
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
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "Description",
        title: "Description",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Name', 'Type']);

onMounted(async () => {
    await getCompanyRoles();
});

watch(() => companyStore.getSelectedCompany, async () => {
    resetRequest();
    await getCompanyRoles();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCompanyRoles();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCompanyRoles();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCompanyRoles();
}

async function getCompanyRoles() {
    let {data, pagination} = await Role.getCompanyRoles(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteRole(companyUserRole, index) {
    Swal.fire({
        title: 'Are you sure? Delete Role?',
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
            let {data, message} = await Role.delete(companyUserRole.Id);
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
            <router-link :to="{name: 'edit-role', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteRole(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
