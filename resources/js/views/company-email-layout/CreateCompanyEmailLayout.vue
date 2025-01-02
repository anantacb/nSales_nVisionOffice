<script setup>
import {onMounted, ref, nextTick} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useFormErrors} from "@/composables/useFormErrors";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const createEmailLayoutRef = ref(null);
let LanguageOptions = ref([]);
let PreviewTemplateObject = ref({});
const isLoading = ref(false);

let Name = ref('');
let LanguageId = ref('');
let Template = ref('');

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

async function createEmailLayout() {
    isLoading.value = true;
    let formData = {
        Name: Name.value,
        LanguageId: LanguageId.value,
        Template: Template.value,
    };

    try {
        let {data, message} = await CompanyEmailLayout.create(companyStore.selectedCompany.Id, formData);
        isLoading.value = false;
        notificationStore.showNotification(message);

        await nextTick();
        await router.push({name: 'company-email-layouts'});
    } catch (error) {
        if (error.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        isLoading.value = false;
    }

}

async function getPreviewTemplateObjectForLayout() {
    isLoading.value = true;
    let {data} = await CompanyEmailLayout.getPreviewTemplateObjectForLayout(companyStore.selectedCompany.Id,);
    PreviewTemplateObject.value = data;
    isLoading.value = false;
}

function setTemplate(newEditorValue) {
    Template.value = newEditorValue;
    resetErrors();
}

function setNewErrors(newErrors) {
    setErrors(newErrors);
}

onMounted(async () => {
    await getAllLanguages();
    await getPreviewTemplateObjectForLayout();
});
</script>

<template>
    <div class="content">
        <Loader :is-loading="isLoading"></Loader>

        <form class="space-y-4" @submit.prevent="createEmailLayout">
            <BaseBlock ref="createEmailLayoutRef" content-full title="Create Email layout">
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
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Name"
                                       placeholder="Name"
                                       required
                                       type="text"
                                       @keyup="resetErrors"
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
                                    id="Language" v-model="LanguageId"
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
                :LanguageId="LanguageId"
                :Template="Template"
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
