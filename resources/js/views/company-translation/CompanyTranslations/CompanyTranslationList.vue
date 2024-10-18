<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import useGridManagement from "@/composables/useGridManagement";
import Language from "@/models/Office/Language";
import CompanyTranslation from "@/models/Company/CompanyTranslation";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";

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
        name: "company_language",
        title: "Language",
        formatter: (language) => {
            return language ? language.Name : '';
        }
    },
    {
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "ElementName",
        title: "ElementName",
        sortField: "ElementName"
    },
    {
        name: "Action",
        title: "Action"
    }
]);
setSearchColumns(['Type', 'ElementName']);

onMounted(() => {
    getCompanyTranslations();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCompanyTranslations();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCompanyTranslations();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCompanyTranslations();
}

async function getCompanyTranslations() {
    let {
        data,
        pagination
    } = await CompanyTranslation.getCompanyTranslations(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCompanyTranslation(companyTranslation, index) {
    Swal.fire({
        title: 'Are you sure? Delete Translation?',
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
            let {
                data,
                message
            } = await CompanyTranslation.delete(companyStore.selectedCompany.Id, companyTranslation.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        resetRequest();
        getCompanyTranslations();
    }
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
            <router-link :to="{name: 'edit-company-translation', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteCompanyTranslation(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
