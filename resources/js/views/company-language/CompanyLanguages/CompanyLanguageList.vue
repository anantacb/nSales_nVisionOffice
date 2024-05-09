<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import useGridManagement from "@/composables/useGridManagement";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import {useCompanyStore} from "@/stores/companyStore";

defineExpose({
    refresh
});

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
        name: "Code",
        title: "Code",
        sortField: "Code"
    },
    {
        name: "Locale",
        title: "Locale",
        sortField: "Locale"
    },
    {
        name: "IsDefault",
        title: "Default",
        formatter: (data) => {
            return data ? "Yes" : "No";
        }
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Name', 'Locale', 'Code']);

onMounted(() => {
    getCompanyLanguages();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCompanyLanguages();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCompanyLanguages();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCompanyLanguages();
}

function refresh() {
    resetRequest();
    getCompanyLanguages();
}

async function getCompanyLanguages() {
    let {data, pagination} = await CompanyLanguage.getCompanyLanguages(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCompanyLanguage(companyLanguage, index) {
    Swal.fire({
        title: 'Are you sure? Delete Language?',
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
            let {data, message} = await CompanyLanguage.delete(companyStore.selectedCompany.Id, companyLanguage.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

function setAsDefaultLanguage(companyLanguageId, index) {
    Swal.fire({
        title: 'Are you sure? Set as Default Language?',
        html: 'Please type <code class="text-danger">Confirm</code> and press delete.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        confirmButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            let {
                data,
                message
            } = await CompanyLanguage.setAsDefaultLanguage(companyStore.selectedCompany.Id, companyLanguageId);
            notificationStore.showNotification(message);
            refresh();
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
            <PopOverButton
                btnClass="btn rounded-pill btn-alt-primary me-1"
                content="Set As Default"
                iconClass="fa fa-heart-circle-bolt"
                @click="setAsDefaultLanguage(props.data.Id)"
            ></PopOverButton>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteCompanyLanguage(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
