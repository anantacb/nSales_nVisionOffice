<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import useGridManagement from "@/composables/useGridManagement";
import CompanyEmailTemplate from "@/models/Company/CompanyEmailTemplate";
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
        name: "ModifiedElementName",
        title: "Element Name",
        sortField: "ElementName"
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
        name: "company_email_layout",
        title: "Layout",
        formatter: (company_email_layout) => {
            return company_email_layout ? company_email_layout.Name : '';
        },
        sortField: "LayoutId"
    },
    {
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(['Name']);

function goToPage(pageNo) {
    setPageNo(pageNo);
    getEmailTemplates();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getEmailTemplates();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getEmailTemplates();
}

async function getEmailTemplates() {
    let {
        data,
        pagination
    } = await CompanyEmailTemplate.getEmailTemplates(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

onMounted(async () => {
    await getEmailTemplates();
});

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        resetRequest();
        await getEmailTemplates();
    }
});

function deleteTemplate(template, index) {
    Swal.fire({
        title: 'Are you sure? Delete Template?',
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
            let {data, message} = await CompanyEmailTemplate.delete(companyStore.selectedCompany.Id, template.Id);
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
            <router-link :to="{name: 'edit-company-email-template', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteTemplate(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
