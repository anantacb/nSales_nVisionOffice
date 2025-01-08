<script setup>
import {onMounted, ref, nextTick} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import EmailLayout from "@/models/Office/EmailLayout";
import Language from "@/models/Office/Language";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";

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
    const {data} = await Language.getAllLanguages();
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
        let {data, message} = await EmailLayout.create(formData);
        isLoading.value = false;
        notificationStore.showNotification(message);

        await nextTick();
        await router.push({name: 'email-layouts'});
    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        isLoading.value = false;
    }

}

async function getPreviewTemplateObjectForLayout() {
    isLoading.value = true;
    let {data} = await EmailLayout.getPreviewTemplateObjectForLayout();
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
