<script setup>
import {onMounted, ref} from "vue";
import {useRoute} from "vue-router";
import ItemOverview from "@/views/item/ItemDetails/ItemOverview.vue";
import ItemTabs from "@/views/item/ItemDetails/ItemTabs.vue";

const route = useRoute();
let backButtonRoute = localStorage.getItem('item-details-back-route') ?? 'items';

let productInfo = ref({});

function setProductInfo(data) {
    productInfo.value = data;
}

onMounted(async () => {
});

</script>

<template>
    <!-- Content Heading -->
    <BaseContentHeading :subtitle="productInfo.Name1" iconForSubTitle="fa fa-check-to-slot">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="mt-3 mt-md-0 ms-md-3 space-x-1">
                        <BackButton :routerName="backButtonRoute"/>
                    </li>
                </ol>
            </nav>
        </template>
    </BaseContentHeading>
    <!-- Page Content -->
    <div class="content pt-0">
        <ItemOverview
            :itemId="route.params.id"
        >
        </ItemOverview>

        <ItemTabs
            @setProductInfo="setProductInfo"
        ></ItemTabs>

    </div>
</template>

<style scoped>

</style>
