<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import Company from "@/models/Office/Company";
import User from "@/models/Office/User";

const notificationStore = useNotificationStore();

let tableData = ref([]);
let tableFields = [
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
];
let bodyHeight = "100vh";
let paginationData = ref(null);
let isLoading = ref(true);
let request = ref({
    search_columns: ['Name', 'Email'],
    //relations: [],
    filters: null,
    order: {},
    pagination: {"page_no": 1, "per_page": 20},
    query: null
});

onMounted(async () => {
    await getDevelopers();
});

function goToPage(pageNo) {
    request.value.pagination.page_no = pageNo;
    getDevelopers();
}

function sortBy({field, order}) {
    request.value.order = [
        {"column": field, "sort": order}
    ];
    request.value.pagination.page_no = 1;
    getDevelopers();
}

function search(query) {
    request.value.query = query;
    request.value.pagination.page_no = 1;
    getDevelopers();
}

async function getDevelopers() {
    let {data, pagination} = await User.getDevelopers(request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCompany(company, index) {
    Swal.fire({
        title: 'Are you sure? Delete Company?',
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
            let {data, message} = await Company.delete(company.Id);
            tableData.value.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
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
        <template v-slot:body-Action="props">
            <PopOverButton
                btnClass="btn rounded-pill btn-alt-primary me-1"
                content="Give Access to All Companies"
                iconClass="far fa-chess-king"
            ></PopOverButton>
        </template>
    </DataGrid>
</template>
