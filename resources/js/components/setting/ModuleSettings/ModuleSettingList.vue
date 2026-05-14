<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Module from "@/models/Office/Module";
import ModuleSetting from "@/models/Office/ModuleSetting";
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
    setSortBy,
    resetRequest
} = useGridManagement();

setTableFields([
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
        sortField: "Name",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },
    {
        name: "DataType",
        title: "Data Type",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },
    {
        name: "Value",
        title: "Value",
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
        title: "ValueExpression",
        formatter: (data) => {
            if (data && data.length > 20) {
                return data.substring(0, 20) + '...'
            } else {
                return data;
            }
        }
    },

    {
        name: "CoreSetting",
        title: "CoreSetting",
        formatter: (data) => {
            return data ? "Yes" : "No";
        }
    },
    {
        name: "Readonly",
        title: "Readonly",
        formatter: (data) => {
            return data ? "Yes" : "No";
        }
    },
    {
        name: "Visible",
        title: "Visible",
        formatter: (data) => {
            return data ? "Yes" : "No";
        }
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
setSearchColumns(['Name']);

onMounted(() => {
    getModuleSettings();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getModuleSettings();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getModuleSettings();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getModuleSettings();
}

async function getModuleSettings() {
    let {data, pagination} = await ModuleSetting.getModuleSettings(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteModuleSetting(module, index) {
    Swal.fire({
        title: 'Are you sure? Delete Setting?',
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
            let {data, message} = await ModuleSetting.delete(module.Id);
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
            <router-link :to="{name: 'edit-setting', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteModuleSetting(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
