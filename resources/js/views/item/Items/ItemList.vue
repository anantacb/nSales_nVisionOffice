<script setup>
import {onMounted, ref, watch, onUnmounted} from 'vue';
import Swal from 'sweetalert2';
import _, {debounce} from 'lodash';  // Use lodash-es for better tree-shaking
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import Item from "@/models/Company/Item";
import useGridManagement from "@/composables/useGridManagement";
import {useFormatter} from "@/composables/useFormatter";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
const {numberFormat} = useFormatter();

const tableData = ref([]);
const paginationData = ref(null);
const isLoading = ref(true);

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
} = useGridManagement();

// Table configuration
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
        formatter: numberFormat
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
watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (_.isEmpty(newSelectedCompany)) {
        return;
    }
    resetRequest();
    getItems();
});

// Methods
async function getItems() {
    try {
        isLoading.value = true;
        const {data, pagination} = await Item.getItems(companyStore.selectedCompany.Id, request.value);
        tableData.value = data;
        paginationData.value = pagination;
    } catch (error) {
        notificationStore.showNotification('Error fetching items', 'error');
        console.error('Failed to fetch items:', error);
    } finally {
        isLoading.value = false;
    }
}

function goToPage(pageNo) {
    setPageNo(pageNo);
    getItems();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getItems();
}

const debouncedSearch = debounce((query) => {
    setSearchQuery(query);
    setPageNo(1);
    getItems();
}, 300);

function search(query) {
    debouncedSearch(query);
}

async function deleteItem(customer, index) {
    try {
        const result = await Swal.fire({
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
            preConfirm: (text) => text === 'Confirm',
            allowOutsideClick: () => !Swal.isLoading()
        });

        if (result.isConfirmed) {
            const {message} = await Item.delete(companyStore.selectedCompany.Id, customer.Id);
            tableData.value = tableData.value.filter((_, i) => i !== index);
            notificationStore.showNotification(message);
        }
    } catch (error) {
        notificationStore.showNotification('Error deleting item', 'error');
        console.error('Failed to delete item:', error);
    }
}

// Cleanup
onUnmounted(() => {
    debouncedSearch.cancel();
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
        <template v-slot:body-image_url="props">
            <img
                v-lazy="props.data.image_urls.ThumbnailSmall"
                v-memo="[props.data.image_urls.ThumbnailSmall]"
                :alt="props.data.Name1"
                class="img-responsive"
                width="75"
            >
        </template>

        <template v-slot:body-Number="props">
            <div v-memo="[props.data.Number, props.data.variant_exists]">
                <i v-if="props.data.variant_exists" class="fa fa-th"></i>
                {{ props.data.Number }}
            </div>
        </template>

        <template v-slot:body-Action="props">
            <ActionButton
                :key="`details_${props.data.Id}`"
                :routeTo="{ name: 'item-details', params: {id: props.data.Id} }"
                actionType="details"
                content="Details"
            />
            <!--<ActionButton
            :key="'delete_'+props.data.Id"
            actionType="delete"
            content="Delete"
            @delete="deleteItem(props.data, props.index)"
            />-->
        </template>
    </DataGrid>
</template>
