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
import CkEditor from "@/components/ui/FormElements/CkEditor.vue";

import CKEditor from "@ckeditor/ckeditor5-vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

// import InlineEditor from '@ckeditor/ckeditor5-build-inline'
// import BalloonEditor from '@ckeditor/ckeditor5-build-balloon'
// import BalloonBlockEditor from '@ckeditor/ckeditor5-build-balloon-block'

import useCompanyInfos from "@/composables/useCompanyInfos";
import {useRoute} from "vue-router";
import router from "@/router";
import Item from "@/models/Company/Item";
import WebShopLanguage from "@/models/Company/WebShopLanguage";
import WebShopText from "@/models/Company/WebShopText";
import ItemAttribute from "@/models/Company/ItemAttribute";
import BaseBlock from "@/components/BaseBlock.vue";
import _ from "lodash";

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
let WebShopTexts = ref([]);
let WebShopTextTypes = ref([
    "Header",
    "SubHeader",
    "Body",
    // "Footer"
]);

let ckeditor = CKEditor.component;
const editorData = ref("<p>Hello CKEditor5!</p>");
const editorConfig = ref({});

const props = defineProps({
    productInfo: {
        type: Object,
        required: true,
    },
    webShopLanguages: {
        type: Array,
        required: true,
    },
});

// async function getAllWebShopLanguages() {
//     let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
//     WebShopLanguages.value = data;
//
//     let options = [{label: 'Select Language', value: ''}];
//
//     data.forEach((language) => {
//         options.push({label: language.Name, value: language.Code});
//     });
//
//     WebShopLanguageOptions.value = options;
// }

async function getProductWebShopTexts() {
    let {data} = await WebShopText.fetchByItem(companyStore.selectedCompany.Id, route.params.id);
    console.log(data);

    props.webShopLanguages.forEach((language) => {
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

function label_text(type) {
    if (type === "Header") {
        return "Name";
    } else if (type === "SubHeader") {
        return "SubHeader";
    } else if (type === "Body") {
        return "Description";
    } else if (type === "Footer") {
        return "Footer";
    } else if (type === "SEOTitle") {
        return "Title";
    } else if (type === "SEODescription") {
        return "Description";
    } else if (type === "SEOKeyword") {
        return "Keywords";
    } else if (type === "SEOOgImage") {
        return "OG Image";
    } else if (type === "SEORobot") {
        return "Robot";
    }
}

function updateItemWebShopTexts() {
    console.log(WebShopTexts.value);

}


onMounted(async () => {
    console.log('Translations');
    TabTranslationsRef.value.statusLoading();

    // await getAllWebShopLanguages();
    // await getProductItemAttributes();

    // isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguages.value = [];
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

                <div v-for="(web_shop_text_form,index) of WebShopTexts"
                     :key="index"
                     class="row ">


                    <label class="col-sm-2 col-form-label col-form-label-sm">{{ web_shop_text_form.LanguageName }}
                        Product
                        {{ label_text(web_shop_text_form.Type) }}</label>

                    <div class="col-sm-6">
                        <input
                            v-if="web_shop_text_form.Type===`Header` || web_shop_text_form.Type===`SubHeader` || web_shop_text_form.Type===`Footer`"
                            v-model="web_shop_text_form.Text"
                            :placeholder="`Enter ${web_shop_text_form.LanguageName} Product ${label_text(web_shop_text_form.Type)}`"
                            class="form-control form-control-sm"
                            type="text">

                        <!--                        <textarea v-if="web_shop_text_form.Type===`Body`"-->
                        <!--                                  v-model="web_shop_text_form.Text"-->
                        <!--                                  class="form-control form-control-sm"-->
                        <!--                        >-->
                        <!--                            </textarea>-->
                        <!--                        <quill-editor v-if="web_shop_text_form.Type===`Body`"-->
                        <!--                                      v-model="web_shop_text_form.Text">-->
                        <!--                        </quill-editor>-->

                        <!--                        <ckeditor v-if="web_shop_text_form.Type===`Body`"-->
                        <!--                                  v-model="web_shop_text_form.Text"-->
                        <!--                                  :config="editorConfig"-->
                        <!--                                  :editor="ClassicEditor"-->
                        <!--                        />-->
                        <!--                        <CkEditor-->
                        <!--                            v-if="web_shop_text_form.Type === 'Body'"-->
                        <!--                            v-model="web_shop_text_form.Text"-->
                        <!--                        />-->

                        <CkEditor v-if="web_shop_text_form.Type===`Body`"
                                  v-model="web_shop_text_form.Text"/>
                    </div>
                    <div v-html="web_shop_text_form.Text"></div>


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
