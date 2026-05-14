<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
import useGridManagement from "@/composables/useGridManagement";

const emit = defineEmits(['startLoading', 'endLoading']);
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
        name: "Name",
        title: "Name",
        sortField: "Name"
    },
    {
        name: "Email",
        title: "Email",
        sortField: "Email"
    },
    {
        name: "InsertTime",
        title: "Insert Time",
        sortField: "InsertTime"
    },
    {
        name: "UpdateTime",
        title: "Update Time",
        sortField: "UpdateTime"
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
setSearchColumns(['Name', 'Email']);

onMounted(async () => {
    await getDevelopers();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getDevelopers();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getDevelopers();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getDevelopers();
}

async function getDevelopers() {
    emit('startLoading');
    let {data, pagination} = await User.getDevelopers(request.value);
    tableData.value = data;
    paginationData.value = pagination;
    emit('endLoading');
}

function tagDeveloperToAllCompanies(userId) {
    Swal.fire({
        title: 'Are you sure?',
        html: 'Please type <code class="text-danger">Confirm</code> and press Confirm.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        cancelButtonColor: 'red',
        confirmButtonColor: 'green',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            emit('startLoading');
            let {data, message} = await User.tagDeveloperToAllCompanies(userId);
            notificationStore.showNotification(message);
            emit('endLoading');
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
                content="Give Access to All Companies"
                iconClass="far fa-chess-king"
                @click="tagDeveloperToAllCompanies(props.data.Id)"
            ></PopOverButton>
        </template>
    </DataGrid>
</template>
