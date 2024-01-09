<script setup>
import {onMounted, ref, watch} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import Item from "@/models/Company/Item";
import useGridManagement from "@/composables/useGridManagement";
import {useFormatter} from "@/composables/useFormatter";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
let {numberFormat} = useFormatter();

let tableData = ref([]);
let paginationData = ref(null);
let isLoading = ref(true);

const {
    tableFields,
    bodyHeight,
    request,
    setTableFields,
    resetRequest,
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy
} = useGridManagement()

setTableFields([
    {
        name: "image_url",
        title: "Image",
    },
    {
        name: "Number",
        title: "SKU",
        sortField: "Number",

    },
    {
        name: "Barcode",
        title: "Barcode",
        sortField: "Barcode",
    },
    {
        name: "Name1",
        title: "Name1",
        sortField: "Name1",
    },
    {
        name: "Group",
        title: "Group",
        sortField: "Group",
    },
    {
        name: "Type",
        title: "Type",
    },
    {
        name: "Colli",
        title: "Pack Size",
    },
    {
        name: "Price",
        title: "Price",
        sortField: "Price",
        formatter: (data) => {
            return numberFormat(data);
        }
    },
    {
        name: "Stock",
        title: "Stock",
        sortField: "Stock",
    },
    {
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(['Number', 'Name1', 'Group']);

onMounted(() => {
    setPageNo(1);
    getItems();
});

watch(() => companyStore.getSelectedCompany, () => {
    resetRequest();
    getItems();
});

function goToPage(pageNo) {
    setPageNo(pageNo);
    getItems();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getItems();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getItems();
}

async function getItems() {
    let {data, pagination} = await Item.getItems(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

function deleteCustomer(customer, index) {
    Swal.fire({
        title: 'Are you sure? Delete Item?',
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
            let {data, message} = await Item.delete(companyStore.selectedCompany.Id, customer.Id);
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
        <template v-slot:body-image_url="props">
            <img v-lazy="props.data.image_urls.ThumbnailSmall"
                 class="img-responsive"
                 width="75">
        </template>

        <template v-slot:body-Number="props">
            <div>
                <i v-if="props.data.variant_exists" class="fa fa-th"></i>
                {{ props.data.Number }}
            </div>
        </template>

        <template v-slot:body-Action="props">
            <!--            <ActionButton
                            :key="'details_'+props.data.Id"
                            :routeTo="{ name: 'customer-details', params: {id: props.data.Id} }"
                            actionType="details"
                            content="Details"
                        />

                        <ActionButton
                            :key="'delete_'+props.data.Id"
                            actionType="delete"
                            content="Delete"
                            @delete="deleteCustomer(props.data, props.index)"
                        />-->
        </template>
    </DataGrid>
</template>
