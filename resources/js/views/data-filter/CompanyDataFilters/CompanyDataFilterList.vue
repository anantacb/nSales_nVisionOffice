<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import DataFilter from "@/models/Office/DataFilter";
import {useCompanyStore} from "@/stores/companyStore";
import useGridManagement from "@/composables/useGridManagement";
import _ from "lodash";

const emit = defineEmits(['showFilterResultModal']);

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
        name: "Type",
        title: "Type",
        sortField: "Type"
    },
    {
        name: "module",
        title: "Module",
        formatter: (module) => {
            return module ? module.Name : '';
        }
    },
    {
        name: "table",
        title: "Table",
        formatter: (table) => {
            return table ? table.Name : '';
        }
    },
    {
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "Value",
        title: "Value",
        sortField: "",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },
    {
        name: "ValueExpression",
        title: "Value Expression",
        sortField: "",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },
    {
        name: "ApplyTo",
        title: "Apply To"
    },
    {
        name: "ApplyOn",
        title: "Applies On",
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
setSearchColumns(['Name', 'Value', 'ValueExpression']);

onMounted(async () => {
    await getDataFilters();
});

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        resetRequest();
        await getDataFilters();
    }
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getDataFilters();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getDataFilters();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getDataFilters();
}

async function getDataFilters() {
    let {data, pagination} = await DataFilter.getCompanyDataFilters(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteDataFilter(dataFilter, index) {
    Swal.fire({
        title: 'Are you sure? Delete Data Filer?',
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
            let {data, message} = await DataFilter.delete(dataFilter.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

function getApplyOnValue(row) {
    let ApplyOnValue = '';
    switch (row.ApplyTo) {
        case 'Application':
            ApplyOnValue = row.application.Name;
            break;
        case 'Company':
            ApplyOnValue = row.company.Name;
            break;
        case 'Role':
            ApplyOnValue = row.role.Name;
            break;
        case 'User':
            ApplyOnValue = row.user.Name;
            break;
        default:
            break;
    }
    return ApplyOnValue;
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
        <template v-slot:body-ApplyOn="props">
            {{ getApplyOnValue(props.data) }}
        </template>
        <template v-slot:body-Action="props">
            <PopOverButton
                btnClass="btn rounded-pill btn-alt-primary me-1"
                content="Filter Result"
                iconClass="far fa-circle-play"
                @click="emit('showFilterResultModal', props.data.Id)"
            ></PopOverButton>
            <router-link :to="{name: 'edit-data-filter', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteDataFilter(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
