<script setup>
import {onMounted, ref} from "vue";
import _ from "lodash";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Language from "@/models/Office/Language";
import EmailLayout from "@/models/Office/EmailLayout";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageOptions = ref([]);
let EmailLayoutModel = ref({});
let PreviewTemplateObject = ref({});
const updateEmailLayoutRef = ref(null);
const isLoading = ref(false)

async function updateEmailLayout() {
    isLoading.value = true;
    let formData = {
        Id: EmailLayoutModel.value.Id,
        Name: EmailLayoutModel.value.Name,
        LanguageId: EmailLayoutModel.value.LanguageId,
        Template: EmailLayoutModel.value.Template,
    };

    try {
        let {data, message} = await EmailLayout.update(formData);
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
    let {data} = await EmailLayout.details(route.params.id);
    EmailLayoutModel.value = data;
    isLoading.value = false;
}

async function getAllLanguages() {
    isLoading.value = true;
    const {data} = await Language.getAllLanguages();
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
    let {data} = await EmailLayout.getPreviewTemplateObjectForLayout();
    PreviewTemplateObject.value = data;
    isLoading.value = false;
}

function setTemplate(newEditorValue) {
    EmailLayoutModel.value.Template = newEditorValue;
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
        <form v-if="!_.isEmpty(EmailLayoutModel)" class="space-y-4" @submit.prevent="updateEmailLayout">
            <!--        <form class="space-y-4" @submit.prevent="updateEmailLayout">-->

            <BaseBlock ref="updateEmailLayoutRef" content-full title="Edit Email Layout">
                <template #options>
                    <router-link :to="{name:'email-layouts'}" class="btn btn-sm btn-outline-info">
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
                                <input id="Name" v-model="EmailLayoutModel.Name"
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
                                    id="Language" v-model="EmailLayoutModel.LanguageId"
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
                :LanguageId="EmailLayoutModel.LanguageId"
                :Template="EmailLayoutModel.Template"
                :TemplateObject="PreviewTemplateObject"
                :errors="errors"
                AppType="office"
                PageType="layout"
                @setNewErrors="setNewErrors"
                @setTemplate="setTemplate"
            >
            </TemplateAndPreview>

            <button class="btn btn-outline-primary btn-sm col-2 mb-5" type="submit">Save</button>
        </form>
    </div>
</template>
