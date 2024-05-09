<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import useGridManagement from "@/composables/useGridManagement";
import Language from "@/models/Office/Language";
import Translation from "@/models/Office/Translation";

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
        name: "language",
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
    getTranslations();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getTranslations();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getTranslations();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getTranslations();
}

async function getTranslations() {
    let {data, pagination} = await Translation.getTranslations(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteTranslation(translation, index) {
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
            let {data, message} = await Translation.delete(translation.Id);
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
            <router-link :to="{name: 'edit-translation', params:{id: props.data.Id}}"
                         class="btn rounded-pill btn-alt-warning me-1">
                <i class="fa fa-pen-alt"></i>
            </router-link>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="deleteTranslation(props.data, props.index)">
                <i class="fa fa-trash-alt"></i>
            </button>
        </template>
    </DataGrid>
</template>
