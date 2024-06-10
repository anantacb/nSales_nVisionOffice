<script setup>
import {onMounted, ref} from "vue";
import User from "@/models/Office/User";
import TableHelper from "@/models/TableHelper";
import Order from "@/models/Company/Order";
import Customer from "@/models/Company/Customer";
import {useAuthStore} from "@/stores/authStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormatter} from "@/composables/useFormatter";
import {useFormErrors} from "@/composables/useFormErrors";
import useGeneralCreate from "@/composables/useGeneralCreate";
import GeneralForm from "@/components/ui/FormElements/GeneralForm.vue";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useRoute} from "vue-router";
import router from "@/router";
import Item from "@/models/Company/Item";
import WebShopLanguage from "@/models/Company/WebShopLanguage";
import ItemAttribute from "@/models/Company/ItemAttribute";
import BaseBlock from "@/components/BaseBlock.vue";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setProductInfo']);

let TabTranslationsRef = ref(null);
let ItemAttributes = ref([]);
let WebShopLanguages = ref([]);
let WebShopLanguageOptions = ref([]);

async function getProductItemAttributes() {
    let {data} = await ItemAttribute.fetchByItem(companyStore.selectedCompany.Id, route.params.id);
    ItemAttributes.value = data;
}

async function getAllWebShopLanguages() {
    let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
    WebShopLanguages.value = data;

    let options = [{label: 'Select Language', value: ''}];

    data.forEach((language) => {
        options.push({label: language.Name, value: language.Code});
    });

    WebShopLanguageOptions.value = options;
}


onMounted(async () => {
    console.log('Translations');
    TabTranslationsRef.value.statusLoading();

    // await getAllWebShopLanguages();
    // await getProductItemAttributes();

    // isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguages.value = [];
    // isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];

    TabTranslationsRef.value.statusNormal();

});
</script>

<template>
    <BaseBlock ref="TabTranslationsRef" content-full>
                <h4 class="fw-normal">Translations Content</h4>
                <p>...</p>

    </BaseBlock>
</template>

<style scoped>
</style>
