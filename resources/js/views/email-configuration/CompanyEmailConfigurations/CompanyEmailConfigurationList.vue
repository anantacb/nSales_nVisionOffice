<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import EmailConfiguration from "@/models/Office/EmailConfiguration";
import {useCompanyStore} from "@/stores/companyStore";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let tableData = ref([]);
let tableFields = [
    {
        name: "module",
        title: "Module",
        formatter: (module) => {
            return module ? module.Name : '';
        }
    },
    {
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "From",
        title: "From",
        sortField: "From"
    },
    {
        name: "To",
        title: "To",
        sortField: "To"
    },
    {
        name: "TemplateType",
        title: "Type",
        sortField: "TemplateType"
    },
    {
        name: "ApplyTo",
        title: "Applies To"
    },
    {
        name: "ApplyOn",
        title: "Applies On"
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
];
let bodyHeight = "100vh";
let paginationData = ref(null);
let isLoading = ref(true);
let request = ref({
    search_columns: ['Name', 'From', 'To', 'Cc', 'Bcc'],
    //relations: [],
    filters: null,
    order: {},
    pagination: {"page_no": 1, "per_page": 20},
    query: null
});

onMounted(() => {
    getEmailConfigurations();
});

watch(() => companyStore.getSelectedCompany, async () => {
    await getEmailConfigurations();
});

function goToPage(pageNo) {
    request.value.pagination.page_no = pageNo;
    getEmailConfigurations();
}

function sortBy({field, order}) {
    request.value.order = [
        {"column": field, "sort": order}
    ];
    request.value.pagination.page_no = 1;
    getEmailConfigurations();
}

function search(query) {
    request.value.query = query;
    request.value.pagination.page_no = 1;
    getEmailConfigurations();
}

async function getEmailConfigurations() {
    let {data, pagination} =
        await EmailConfiguration.getCompanyEmailConfigurations(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteEmailConfiguration(table, index) {
    Swal.fire({
        title: 'Are you sure? Delete Email Configuration?',
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
            let {data, message} = await EmailConfiguration.delete(table.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

function getApplyOnValue(row) {
    let ApplyOnValue = '';
    switch (row.ApplyTo) {
        case 'Application':
            ApplyOnValue = row.application ? row.application.Name : '';
            break;
        case 'Company':
            ApplyOnValue = row.company ? row.company.Name : '';
            break;
        case 'Role':
            ApplyOnValue = row.role ? row.role.Name : '';
            break;
        case 'User':
            ApplyOnValue = row.user ? row.user.Name : '';
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
        :height="`${bodyHeight - 115}px`"
        :isLoading="isLoading"
        :pagination="paginationData"
        :searchable="true"
        :tabledata="tableData"
        :tablefields="tableFields"
        @expand=""
        @paginate="goToPage"
        @search="search"
        @sortBy="sortBy"
    >
        <template v-slot:body-ApplyOn="props">
            {{ getApplyOnValue(props.data) }}
        </template>
        <template v-slot:body-Action="props">
            <router-link :to="{name: 'edit-email-configuration', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteEmailConfiguration(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
