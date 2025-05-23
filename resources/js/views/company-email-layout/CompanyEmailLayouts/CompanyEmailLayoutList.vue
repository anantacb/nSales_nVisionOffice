<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import useGridManagement from "@/composables/useGridManagement";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";

const companyStore = useCompanyStore();
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
        name: "company_language",
        title: "Language",
        formatter: (company_language) => {
            return company_language ? company_language.Name : '';
        },
        sortField: "LanguageId"
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Name']);

onMounted(() => {
    getEmailLayouts();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    console.log(newSelectedCompany);
    if (!_.isEmpty(newSelectedCompany)) {
        getEmailLayouts();
    }
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getEmailLayouts();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getEmailLayouts();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getEmailLayouts();
}

async function getEmailLayouts() {
    let {data, pagination} = await CompanyEmailLayout.getEmailLayouts(companyStore.selectedCompany.Id, request.value);
    // console.log(data);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteLayout(layout, index) {
    Swal.fire({
        title: 'Are you sure? Delete Layout?',
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
            let {data, message} = await CompanyEmailLayout.delete(companyStore.selectedCompany.Id, layout.Id);
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
            <router-link :to="{name: 'edit-company-email-layout', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteLayout(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
