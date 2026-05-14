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

let TabSeoRef = ref(null);
let ItemAttributes = ref([]);
// let CompanyLanguages = ref([]);
let WebShopTextSeoForms = ref([]);
let WebShopTextSeoTypes = ref([
    'SEOTitle',
    'SEODescription',
    'SEOKeyword',
    'SEORobot'
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

    let index = _.findIndex(WebShopTextSeoForms.value, function (webShopTextSeoForm) {
        return webShopText.Type === webShopTextSeoForm.Type &&
            webShopText.Language === webShopTextSeoForm.Language &&
            webShopText.ElementType === webShopTextSeoForm.ElementType;
    });

    // console.log('index: ' + index)
    // console.log(WebShopTextSeoForms.value[index]);

    if (index >= 0) {
        // console.log('index: ' + index);
        WebShopTextSeoForms.value[index].Id = webShopText.Id;
        WebShopTextSeoForms.value[index].Text = webShopText.Text;
    }

}

async function getProductWebShopTexts() {
    let {data} = await WebShopText.fetchByItem(companyStore.selectedCompany.Id, route.params.id);

    props.companyLanguages.forEach((language) => {
        WebShopTextSeoTypes.value.forEach((type) => {
            WebShopTextSeoForms.value.push({
                ElementType: "Item",
                Id: "",
                Language: language.Code,
                Text: type === `SEORobot` ? `follow,index` : "",
                Type: type,
                LanguageName: language.Name
            });
        });
    });



    // if (this.moduleEnabled(`WSSEO`)) {
    //     this.web_shop_text_seo_types.forEach((type) => {
    //         this.web_shop_text_seo_forms.push(new WebShopText({
    //             ElementType: "Item",
    //             Id: "",
    //             Language: item.Code,
    //             Text: type === `SEORobot` ? `follow,index` : "",
    //             Type: type,
    //             LanguageName: item.Name
    //         }));
    //     });
    // }

    data.forEach((webShopText) => {
        modifyWebShopText(webShopText);

        // if (this.moduleEnabled(`WSSEO`)) {
        //     let index_seo = _.findIndex(this.web_shop_text_seo_forms, function (webShopTextSeoForm) {
        //         return web_shop_text.Type === webShopTextSeoForm.Type &&
        //             web_shop_text.Language === webShopTextSeoForm.Language &&
        //             web_shop_text.ElementType === webShopTextSeoForm.ElementType;
        //     });
        //
        //     if (index_seo >= 0) {
        //         this.web_shop_text_seo_forms[index_seo].Id = web_shop_text.Id;
        //         this.web_shop_text_seo_forms[index_seo].Text = web_shop_text.Text;
        //     }
        // }
    });
    console.log(WebShopTextSeoForms);
}

async function updateItemWebShopTexts() {
    TabSeoRef.value.statusLoading();

    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        ItemId: route.params.id,
        ItemNumber: props.itemInfo.Number,
        WebShopTextSeoForms: WebShopTextSeoForms.value,
    };

    try {
        let {data, message} = await WebShopText.updateByItem(formData);

        data.forEach((webShopText) => {
            modifyWebShopText(webShopText);
        });

        TabSeoRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        TabSeoRef.value.statusNormal();
    }

}

onMounted(async () => {
    TabSeoRef.value.statusLoading();

    // await getAllCompanyLanguages();
    // await getProductItemAttributes();

    // isModuleEnabled('Translation') ? await getAllCompanyLanguages() : CompanyLanguages.value = [];
    // isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];
    isModuleEnabled('WSSEO') ? await getProductWebShopTexts() : WebShopTextSeoForms.value = [];

    TabSeoRef.value.statusNormal();

});
</script>

<template>
    <BaseBlock ref="TabSeoRef" content-full>
        <form class="space-y-4" @submit.prevent="updateItemWebShopTexts">

            <!--            <div class="row">-->

            <div class="col-lg-12 space-y-2">

                <div v-for="(webShopTextSeoForm,index) of WebShopTextSeoForms"
                     :key="index"
                     class="row ">

                    <label class="col-sm-2 col-form-label col-form-label-sm">
                        {{ `${webShopTextSeoForm.LanguageName} Product ${LabelText[webShopTextSeoForm.Type]}` }}
                    </label>

                    <div class="col-sm-6">
                        <input
                            v-if="webShopTextSeoForm.Type===`SEOTitle`"
                            v-model="webShopTextSeoForm.Text"
                            :placeholder="`Enter ${webShopTextSeoForm.LanguageName} Product ${LabelText[webShopTextSeoForm.Type]}`"
                            class="form-control form-control-sm"
                            type="text">

                        <p v-if="webShopTextSeoForm.Type===`SEOTitle`" class="font-italic">
                            <span class="text-info" v-text="webShopTextSeoForm.Text.length"></span>
                            of 70 characters used</p>

                        <textarea
                            v-if="webShopTextSeoForm.Type===`SEODescription` || webShopTextSeoForm.Type===`SEOKeyword`"
                            v-model="webShopTextSeoForm.Text"
                            :placeholder="`Enter ${webShopTextSeoForm.LanguageName} Product ${LabelText[webShopTextSeoForm.Type]}`"
                            class="form-control form-control-sm"
                            maxlength="320"
                            rows="4">
                        </textarea>

                        <p v-if="webShopTextSeoForm.Type===`SEODescription`" class="font-italic">
                            <span class="text-info" v-text="webShopTextSeoForm.Text.length"></span>
                            of 320 characters used
                        </p>

<!--                        <textarea v-if="webShopTextSeoForm.Type===`SEOKeyword`"-->
<!--                                  v-model="webShopTextSeoForm.Text"-->
<!--                                  class="form-control"-->
<!--                                  rows="3">-->
<!--                        </textarea>-->

                        <select v-if="webShopTextSeoForm.Type===`SEORobot`"
                                v-model="webShopTextSeoForm.Text"
                                class="form-control custom-select form-control-sm"
                                @change="resetErrors(`seo`)">
                            <option value="follow,index">Follow Index</option>
                            <option value="nofollow,noindex">Nofollow Noindex</option>
                        </select>

                        <div v-if="errors.seo">
                            <small
                                v-for="error in errors.seo[`web_shop_texts.${index}.Text`]"
                                class="form-text text-danger"
                                v-text="error">
                            </small>
                        </div>

<!--                        <CkEditor v-if="webShopTextSeoForm.Type===`Body`"-->
<!--                                  v-model="webShopTextSeoForm.Text"/>-->
                    </div>

                </div>


                <button class="btn btn-outline-primary btn-sm col-2" type="submit">
                    Update SEO Text
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
