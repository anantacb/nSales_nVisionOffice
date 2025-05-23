<template>
    <div v-if="isLoading" class="position-relative max-h-60vh h-100">
        <Loader :is-loading="isLoading"></Loader>
    </div>
    <div v-else-if="itemGroup">
        <h4 class="d-flex justify-content-between">
            {{ itemGroup?.Name }}
        </h4>
        <div class="max-h-60vh overflow-auto">
            <div v-if="items && items.length > 0 && !priceExist" class="alert alert-warning p-2">Price not found</div>
            <div v-if="items && items.length > 0 && !mediaExist" class="alert alert-warning p-2">Media not found</div>
            <table class="table">
                <thead class="table-dark">
                <tr>
                    <td>Product</td>
                    <td>Number</td>
                    <td>Price</td>
                </tr>
                </thead>
                <tbody>
                <template v-if="items && items.length > 0">
                    <tr v-for="item in items">
                        <td>
                            <img v-if="item.Media && item.Media.length > 0" :src="item.Media[0].FullPath"
                                 alt="" class="img-fluid" width="50">
                            {{ item.Name1 }}
                        </td>
                        <td>{{ item.Number }}</td>
                        <td>{{ item.Price.Price }}</td>
                    </tr>
                </template>
                <tr v-else>
                    <td class="text-center" colspan="4">No items found in the parent itemgroups!!!</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div v-else class="m-8 text-center">
        <h6>No product category added yet!</h6>
        <a class="btn btn-primary" href="https://app.nsales.io/e-commerce/itemgroups" target="_blank">Add product
            category</a>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import B2bGqlApi from "@/models/Company/B2bGqlApi";
import Loader from "@/components/ui/Loader/Loader.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)

onMounted(() => {
    getItemGroupsAndItem();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getItemGroupsAndItem();
    }
});

const itemGroup = ref(null)
const items = ref([])

const priceExist = ref(false)
const mediaExist = ref(false)

const getItemGroupsAndItem = async () => {
    try {
        isLoading.value = true

        const {data} = await B2bGqlApi.getItemGroupsAndItem(companyStore.selectedCompany.Id)
        itemGroup.value = data.itemgroup
        items.value = data.items

        for (let item of items.value) {
            priceExist.value = item.Price.Price ? true : priceExist.value
            mediaExist.value = item.Media && item.Media.length > 0 && item.Media[0].FullPath ? true : mediaExist.value
        }

        // if (priceExist.value && mediaExist.value) {
        //     emits("complete", true)
        // }
        emits("complete", true);

        isLoading.value = false
    } catch (err) {
        isLoading.value = false
    }

}
</script>
<style scoped>
.max-h-60vh {
    max-height: 60vh;
}
</style>
