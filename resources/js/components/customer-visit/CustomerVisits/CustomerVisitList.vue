<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import CustomerVisit from "@/models/Company/CustomerVisit";
import {useCompanyStore} from "@/stores/companyStore";
import {useFormatter} from "@/composables/useFormatter";
import useGridManagement from "@/composables/useGridManagement";
import FlatPicker from "vue-flatpickr-component";
import _ from "lodash";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let tableData = ref([]);
let paginationData = ref(null);
let isLoading = ref(true);
let filterLists = ref({});
let {numberFormat, dateFormat} = useFormatter();
let dateFormatStr = ref('DD-MM-YYYY');
let filterOptionsForAccount = ref();
let filterOptionsForEmployee = ref();
let configForFlatPicker = ref({
    wrap: true,
    altFormat: 'j M Y',
    dateFormat: 'Y-m-d',
    // maxDate: "today",
    // altInput: true,
    // allowInput: true,
});

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
        name: "Account",
        title: "Account",
        sortField: "Account"
    },
    {
        name: "CustomerBillingName",
        title: "Name",
    },
    {
        name: "DateStart",
        title: "Start Date",
        formatter: (data) => {
            return dateFormat(data, dateFormatStr.value);
        },
        sortField: "DateStart"
    },

    {
        name: "DateEnd",
        title: "End Date",
        formatter: (data) => {
            return dateFormat(data, dateFormatStr.value);
        },
        sortField: "DateEnd"
    },
    {
        name: "Employee",
        title: "Employee"
    },
    {
        name: "Note",
        title: "Note"
    }
]);

setSearchColumns(['Name', 'CustomerAccount', 'OrderNumber']);

onMounted(() => {
    setPageNo(1);
    getCustomerVisits();
    resetFilterLists();
    getFilterOptions();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        resetRequest();
        resetFilterLists();
        getCustomerVisits();
        getFilterOptions();
    }
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getCustomerVisits();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getCustomerVisits();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getCustomerVisits();
}

function resetFilterLists() {
    filterLists.value = {Account: '', Employee: '', DateStart: null};
}

function resetFilters() {
    request.value.filters = [];
}

function setFilters(filterColumn) {
    request.value.filters.push({
        'column': filterColumn,
        'operator': '=',
        'values': filterLists.value[filterColumn]
    });
}

async function getCustomerVisits() {
    let {data, pagination} = await CustomerVisit.getCustomerVisits(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

async function getDistinctValue(columnName) {
    let {data} = await CustomerVisit.getDistinctValue(companyStore.selectedCompany.Id, columnName);
    return data;
}

async function getFilterOptions() {
    filterOptionsForAccount.value = await getDistinctValue(`Account`);
    filterOptionsForEmployee.value = await getDistinctValue(`Employee`);
}

function clearDateStart() {
    filterLists.value.DateStart = null;
}

function filtersOptionChange() {
    let filterTempObj = request.value.filters;
    resetFilters();

    if (filterLists.value.Account) {
        setFilters('Account')
    }

    if (filterLists.value.Employee) {
        setFilters('Employee')
    }

    if (filterLists.value.DateStart) {
        setFilters('DateStart');
    }

    // if any changes in Filter
    if (!_.isEqual(filterTempObj, request.value.filters)) {
        setPageNo(1);
        getCustomerVisits();
    }

}

function deleteOrder(module, index) {
    Swal.fire({
        title: 'Are you sure? Delete CustomerVisit?',
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
            let {data, message} = await CustomerVisit.delete(module.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

</script>

<template>

    <!-- Filters Block -->
    <div class="form-group filter-group">
        <div class="row">
            <div class="col-3">
                <select v-model="filterLists.Account"
                        class="form-control fs-sm"
                        @change="filtersOptionChange">
                    <option value="">Account</option>
                    <option v-for="(accountOption, key) of filterOptionsForAccount" :key="accountOption.value+'_'+key"
                            :value="accountOption.value"> {{ accountOption.label }}
                    </option>
                    <option value="">Reset</option>
                </select>
            </div>
            <div class="col-3">
                <select v-model="filterLists.Employee"
                        class="form-control fs-sm"
                        @change="filtersOptionChange">
                    <option value="">Employee</option>
                    <option v-for="(employeeOption, key) of filterOptionsForEmployee"
                            :key="employeeOption.value+'_'+key"
                            :value="employeeOption.value"> {{ employeeOption.label }}
                    </option>
                    <option value="">Reset</option>
                </select>
            </div>

            <div class="col-3">
                <div class="input-group">
                    <FlatPicker
                        v-model="filterLists.DateStart"
                        :config="configForFlatPicker"
                        class="form-control fs-sm"
                        name="DateStart"
                        placeholder="Start Date"
                        @onChange="filtersOptionChange"
                    />
                    <div class="input-group-append">
                        <button class="btn btn-default" data-toggle title="Toggle" type="button">
                            <i class="fa fa-calendar"/>
                            <span aria-hidden="true" class="sr-only">Toggle</span>
                        </button>
                        <button class="btn btn-default" data-clear title="Clear" type="button" @click="clearDateStart">
                            <i class="fa fa-times"/>
                            <span aria-hidden="true" class="sr-only">Clear</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Filters Block -->

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
            <!-- <router-link :to="{name: 'customer-visit-details', params:{id: props.data.UUID}}"
                             class="btn rounded-pill btn-alt-warning me-1">
                    <i class="fa fa-clipboard-list"></i>
                </router-link>-->

            <!--            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                                @click="deleteOrder(props.data, props.index)">
                            <i class="fa fa-trash-alt"></i>
                        </button>-->
        </template>
    </DataGrid>
</template>

<style scoped>
@media only screen and (min-width: 768px) {
    .filter-group {
        bottom: -42px;
        margin-top: -35px;
        position: relative;
        width: 70%;
        z-index: 999;
    }
}

.input-group > :not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
    border-radius: 0.375rem;
}

.input-group .btn {
    padding-right: 0;
    padding-left: 8px;
}

.dark-mode .input-group .btn {
    color: #ffffff;
}

</style>
