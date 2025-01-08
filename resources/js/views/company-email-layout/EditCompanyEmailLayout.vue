<script setup>
import {onMounted, ref} from "vue";
import _ from "lodash";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import {useCompanyStore} from "@/stores/companyStore";
import CompanyLanguage from "@/models/Company/CompanyLanguage";

const route = useRoute();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageOptions = ref([]);
let CompanyEmailLayoutModel = ref({});
let PreviewTemplateObject = ref({});
const updateEmailLayoutRef = ref(null);
const isLoading = ref(false)

async function updateEmailLayout() {
    isLoading.value = true;
    let formData = {
        Id: CompanyEmailLayoutModel.value.Id,
        Name: CompanyEmailLayoutModel.value.Name,
        LanguageId: CompanyEmailLayoutModel.value.LanguageId,
        Template: CompanyEmailLayoutModel.value.Template,
    };

    try {
        let {data, message} = await CompanyEmailLayout.update(companyStore.selectedCompany.Id, formData);
        notificationStore.showNotification(message);
    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        isLoading.value = false;
    }
}

async function getEmailLayoutDetails() {
    isLoading.value = true;
    let {data} = await CompanyEmailLayout.details(companyStore.selectedCompany.Id, route.params.id);
    CompanyEmailLayoutModel.value = data;
    isLoading.value = false;
}

async function getAllLanguages() {
    isLoading.value = true;
    const {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id);
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    LanguageOptions.value = options;
    isLoading.value = false;
}

async function getPreviewTemplateObjectForLayout() {
    isLoading.value = true;
    let {data} = await CompanyEmailLayout.getPreviewTemplateObjectForLayout(companyStore.selectedCompany.Id);
    PreviewTemplateObject.value = data;
    isLoading.value = false;
}

function setTemplate(newEditorValue) {
    CompanyEmailLayoutModel.value.Template = newEditorValue;
    resetErrors();
}

function setNewErrors(newErrors) {
    setErrors(newErrors);
}

onMounted(async () => {
    // updateEmailLayoutRef.value.statusLoading();
    // isLoading.value = true;
    await getEmailLayoutDetails();
    await getAllLanguages();
    await getPreviewTemplateObjectForLayout();
    // updateEmailLayoutRef.value.statusNormal();
    // isLoading.value = false;
});

</script>

<template>
    <div class="content">
        <Loader :is-loading="isLoading"></Loader>
        <form v-if="!_.isEmpty(CompanyEmailLayoutModel)" class="space-y-4" @submit.prevent="updateEmailLayout">

            <BaseBlock ref="updateEmailLayoutRef" content-full title="Edit Email layout">
                <template #options>
                    <router-link :to="{name:'company-email-layouts'}" class="btn btn-sm btn-outline-info">
                        <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                    </router-link>
                </template>

                <div class="row">
                    <div class="col-lg-6 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="CompanyEmailLayoutModel.Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Name"
                                       placeholder="Name"
                                       required
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-6 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select
                                    id="Language" v-model="CompanyEmailLayoutModel.LanguageId"
                                    :options="LanguageOptions"
                                    :required="true"
                                    :select-class="errors.LanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                    name="Language"
                                    @change="resetErrors"
                                />
                                <InputErrorMessages v-if="errors.LanguageId"
                                                    :errorMessages="errors.LanguageId"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>
            </BaseBlock>

            <TemplateAndPreview
                :LanguageId="CompanyEmailLayoutModel.LanguageId"
                :Template="CompanyEmailLayoutModel.Template"
                :TemplateObject="PreviewTemplateObject"
                :errors="errors"
                AppType="company"
                PageType="layout"
                @setNewErrors="setNewErrors"
                @setTemplate="setTemplate"
            >
            </TemplateAndPreview>

            <button class="btn btn-outline-primary btn-sm col-2 mb-5" type="submit">Save</button>
        </form>
    </div>
</template>
