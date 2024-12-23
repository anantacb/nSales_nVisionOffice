<script setup>
import {onMounted, ref, watch} from "vue";
import User from "@/models/Office/User";
import TableHelper from "@/models/TableHelper";
import Order from "@/models/Company/Order";
import Customer from "@/models/Company/Customer";
import {useAuthStore} from "@/stores/authStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormatter} from "@/composables/useFormatter";
import {useCompanyLanguage} from "@/composables/useCompanyLanguage";
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
import ItemAttributes from "@/views/item/ItemDetails/TabAttributes.vue";
import ItemOverview from "@/views/item/ItemDetails/ItemOverview.vue";
import TabDetails from "@/views/item/ItemDetails/TabDetails.vue";
import TabAttributes from "@/views/item/ItemDetails/TabAttributes.vue";
import TabTranslations from "@/views/item/ItemDetails/TabTranslations.vue";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import TabSeo from "@/views/item/ItemDetails/TabSeo.vue";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
let {
    CompanyLanguages,
    CompanyLanguageOptions,
    getAllCompanyLanguages,
    getCompanyLanguageOptions
} = useCompanyLanguage();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setItemInfo']);

const Tabs = ref(['Details']);
const currentTab = ref('Details');
let itemInfo = ref({});
// let CompanyLanguages = ref([]);
// let CompanyLanguageOptions = ref([]);


async function setItemInfo(data) {
    itemInfo.value = data;
    // console.log('ItemTabs'+data);
    emit('setItemInfo', data);
}

async function setTabs() {
    // Tabs.value.push('Details');

    // if (isModuleEnabled('ItemVariant')
    //     && itemInfo.value.variant_exists
    // ) {
    //     Tabs.value.push('Variants');
    // }

    if (isModuleEnabled('Itemattribute')) {
        Tabs.value.push('Attributes');
    }

    if (isModuleEnabled('Translation') && isModuleEnabled(`WSPage`)) {
        Tabs.value.push('Translations');
    }

    if (isModuleEnabled('WSSEO')) {
        Tabs.value.push('SEO');
    }

    if (isModuleEnabled('Pricegroup')) {
        Tabs.value.push('Pricegroup');
    }

    if (isModuleEnabled('Shopify')) {
        Tabs.value.push('Shopify');
    }

    if (isModuleEnabled('ItemVariantDescription')) {
        Tabs.value.push('Special Variants');
    }
    // console.log(Tabs);
}

async function setVariantTab() {
    if (isModuleEnabled('ItemVariant') && itemInfo.value.variant_exists) {
        // Tabs.value.push('Variants');
        Tabs.value = [...Tabs.value.slice(0, 1), 'Variants', ...Tabs.value.slice(1)];
    }
    // console.log(Tabs);
}


// async function getAllCompanyLanguages() {
//     // let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
//     let {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id, route.params.id);
//     console.log(data);
//     CompanyLanguages.value = data;
//
//     let options = [{label: 'Select Language', value: ''}];
//     data.forEach((language) => {
//         options.push({label: language.Name, value: language.Code});
//     });
//     CompanyLanguageOptions.value = options;
// }


watch(itemInfo, async (newItemInfo) => {
    await setVariantTab();
});

onMounted(async () => {
    await setTabs();
    isModuleEnabled('Translation') ? await getAllCompanyLanguages() : CompanyLanguages.value = [];
});

</script>

<template>
    <BaseBlock>
        <template #content>
            <ul class="nav nav-tabs nav-tabs-block " role="tablist">
                <li v-for="tab in Tabs"
                    :key="'tab-'+tab"
                    class="nav-item"
                >
                    <button
                        :id="`btabs-static-${tab}-tab`"
                        :aria-controls="`btabs-static-${tab}`"
                        :aria-selected="currentTab === tab"
                        :class="['nav-link', { active: currentTab === tab }]"
                        :data-bs-target="`#btabs-static-${tab}`"
                        data-bs-toggle="tab"
                        role="tab"
                        @click="currentTab = tab"
                    >
                        {{ tab }}
                    </button>
                </li>
            </ul>

            <!--tab-content-->
            <div class="block-content tab-content">
                <template v-for="(tab, index) in Tabs" :key="'content-'+tab"
                >
                    <div
                        :id="`btabs-static-${tab}`"
                        :aria-labelledby="`btabs-static-${tab}-tab`"
                        :class="['tab-pane', { active: currentTab === tab }]"
                        role="tabpanel"
                        tabindex="0"
                    >
                        <!--                        <h4 class="fw-normal">{{ tab }}</h4>-->
                        <!--                        <p>...</p>-->

                        <TabDetails v-if="tab === 'Details' && currentTab === tab "
                                    @setItemInfo="setItemInfo"
                        >
                        </TabDetails>

                        <TabAttributes v-if="tab === 'Attributes' && currentTab === tab "
                                       :companyLanguages='CompanyLanguages'
                                       :itemInfo='itemInfo'
                        >
                        </TabAttributes>

                        <TabTranslations v-if="tab === 'Translations' && currentTab === tab "
                                         :companyLanguages='CompanyLanguages'
                                         :itemInfo='itemInfo'>
                        </TabTranslations>

                        <TabSeo v-if="tab === 'SEO' && currentTab === tab "
                                         :companyLanguages='CompanyLanguages'
                                         :itemInfo='itemInfo'>
                        </TabSeo>

                    </div>
                </template>
            </div>
            <!--tab-content end-->

        </template>
    </BaseBlock>
</template>

<style scoped>
.block-content {
    padding: 0;
}
</style>
