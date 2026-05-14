<script setup>
import {onMounted, ref} from "vue";
import {useAuthStore} from "@/stores/authStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormatter} from "@/composables/useFormatter";
import {useFormErrors} from "@/composables/useFormErrors";
import CkEditor from "@/components/ui/FormElements/CkEditor.vue";

import useCompanyInfos from "@/composables/useCompanyInfos";
import {useRoute} from "vue-router";

import WebShopText from "@/models/Company/WebShopText";
import BaseBlock from "@/components/BaseBlock.vue";
import _ from "lodash";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setItemInfo']);

let TabTranslationsRef = ref(null);
let ItemAttributes = ref([]);
// let CompanyLanguages = ref([]);
let WebShopTexts = ref([]);
let WebShopTextTypes = ref([
    "Header",
    "SubHeader",
    "Body",
    // "Footer"
]);

const props = defineProps({
    itemInfo: {
        type: Object,
        required: true,
    },
    companyLanguages: {
        type: Array,
        required: true,
    },
});

const LabelText = ref({
    'Header': 'Name',
    'SubHeader': 'Subheader',
    'Body': 'Description',
    'Footer': 'Footer',
    'SEOTitle': 'Title',
    'SEODescription': 'Description',
    'SEOKeyword': 'Keywords',
    'SEOOgImage': 'OG Image',
    'SEORobot': 'Robot'
});

function modifyWebShopText(webShopText) {

    let index = _.findIndex(WebShopTexts.value, function (webShopTextForm) {
        return webShopText.Type === webShopTextForm.Type &&
            webShopText.Language === webShopTextForm.Language &&
            webShopText.ElementType === webShopTextForm.ElementType;
    });

    // console.log('index: ' + index)
    // console.log(WebShopTexts.value[index]);

    if (index >= 0) {
        // console.log('index: ' + index);
        WebShopTexts.value[index].Id = webShopText.Id;
        WebShopTexts.value[index].Text = webShopText.Text;
    }

}

async function getProductWebShopTexts() {
    let {data} = await WebShopText.fetchByItem(companyStore.selectedCompany.Id, route.params.id);

    props.companyLanguages.forEach((language) => {
        WebShopTextTypes.value.forEach((type) => {
            WebShopTexts.value.push({
                ElementType: "Item",
                Id: "",
                Language: language.Code,
                Text: "",
                Type: type,
                LanguageName: language.Name
            });
        });
    });

    data.forEach((webShopText) => {
        modifyWebShopText(webShopText);

        // if (this.moduleEnabled(`WSSEO`)) {
        //     let index_seo = _.findIndex(this.web_shop_text_seo_forms, function (web_shop_text_seo_form) {
        //         return web_shop_text.Type === web_shop_text_seo_form.Type &&
        //             web_shop_text.Language === web_shop_text_seo_form.Language &&
        //             web_shop_text.ElementType === web_shop_text_seo_form.ElementType;
        //     });
        //
        //     if (index_seo >= 0) {
        //         this.web_shop_text_seo_forms[index_seo].Id = web_shop_text.Id;
        //         this.web_shop_text_seo_forms[index_seo].Text = web_shop_text.Text;
        //     }
        // }
    });
    // console.log(WebShopTexts);
}

async function updateItemWebShopTexts() {
    TabTranslationsRef.value.statusLoading();

    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        ItemId: route.params.id,
        ItemNumber: props.itemInfo.Number,
        WebShopTexts: WebShopTexts.value,
    };

    try {
        let {data, message} = await WebShopText.updateByItem(formData);

        data.forEach((webShopText) => {
            modifyWebShopText(webShopText);
        });

        TabTranslationsRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        TabTranslationsRef.value.statusNormal();
    }

}

onMounted(async () => {
    TabTranslationsRef.value.statusLoading();

    // await getAllCompanyLanguages();
    // await getProductItemAttributes();

    // isModuleEnabled('Translation') ? await getAllCompanyLanguages() : CompanyLanguages.value = [];
    // isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];
    isModuleEnabled('WSPage') ? await getProductWebShopTexts() : WebShopTexts.value = [];

    TabTranslationsRef.value.statusNormal();

});
</script>

<template>
    <BaseBlock ref="TabTranslationsRef" content-full>
        <form class="space-y-4" @submit.prevent="updateItemWebShopTexts">

            <!--            <div class="row">-->

            <div class="col-lg-12 space-y-2">

                <div v-for="(webShopTextForm,index) of WebShopTexts"
                     :key="index"
                     class="row ">

                    <label class="col-sm-2 col-form-label col-form-label-sm">
                        {{ `${webShopTextForm.LanguageName} Product ${LabelText[webShopTextForm.Type]}` }}
                    </label>

                    <div class="col-sm-6">
                        <input
                            v-if="webShopTextForm.Type===`Header` || webShopTextForm.Type===`SubHeader` || webShopTextForm.Type===`Footer`"
                            v-model="webShopTextForm.Text"
                            :placeholder="`Enter ${webShopTextForm.LanguageName} Product ${LabelText[webShopTextForm.Type]}`"
                            class="form-control form-control-sm"
                            type="text">

                        <CkEditor v-if="webShopTextForm.Type===`Body`"
                                  v-model="webShopTextForm.Text"/>
                    </div>

                </div>


                <button class="btn btn-outline-primary btn-sm col-2" type="submit">
                    Update Text
                </button>
                <!--                <button-->
                <!--                    class="btn btn-primary"-->
                <!--                    type="submit">-->
                <!--                    Update Text-->
                <!--                </button>-->


            </div>

            <!--            </div>-->

        </form>
    </BaseBlock>
</template>

<style scoped>
</style>
