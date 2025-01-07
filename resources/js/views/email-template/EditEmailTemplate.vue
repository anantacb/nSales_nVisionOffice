<script setup>
import {computed, onMounted, ref} from "vue";
import _ from "lodash";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Language from "@/models/Office/Language";
import EmailLayout from "@/models/Office/EmailLayout";
import EmailTemplate from "@/models/Office/EmailTemplate";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageOptions = ref([]);
let EmailEventOptions = ref([]);
let LayoutOptions = ref([]);
let EmailTemplateModel = ref({});
let EmailEvents = ref([]);
const updateEmailLayoutRef = ref(null);
const isLoading = ref(false)

function setTemplate(newEditorValue) {
    EmailTemplateModel.value.Template = newEditorValue;
    resetErrors();
}

function setNewErrors(newErrors) {
    setErrors(newErrors);
}

async function updateEmailTemplate() {
    isLoading.value = true;
    let formData = {
        Id: EmailTemplateModel.value.Id,
        ElementName: EmailTemplateModel.value.ElementName,
        LayoutId: EmailTemplateModel.value.LayoutId,
        LanguageId: EmailTemplateModel.value.LanguageId,
        Subject: EmailTemplateModel.value.Subject,
        Template: EmailTemplateModel.value.Template,
    };

    try {
        let {data, message} = await EmailTemplate.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        isLoading.value = false;
    }
}

async function getEmailTemplateDetails() {
    isLoading.value = true;
    let {data} = await EmailTemplate.details(route.params.id);
    EmailTemplateModel.value = data;
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

async function getEmailEvents() {
    isLoading.value = true;
    let {data} = await EmailTemplate.getEmailEvents();
    EmailEvents.value = data;
    let options = [{label: 'Select Element Name', value: ''}];

    Object.entries(EmailEvents.value).forEach(([emailEventKey, emailEvent]) => {
        let option = {label: emailEvent.Title, value: emailEventKey};
        options.push(option);
    });

    EmailEventOptions.value = options;
    isLoading.value = false;
}

async function getLayoutOptionsByLanguage(initialLoad = false) {
    if (!initialLoad) {
        EmailTemplateModel.value.LayoutId = '';
    }

    let {data} = await EmailLayout.getLayoutOptionsByLanguage(EmailTemplateModel.value.LanguageId);
    let options = [{label: 'Select Layout', value: ''}];
    data.forEach((layout) => {
        let option = {label: layout.Name, value: layout.Id};
        options.push(option);
    });
    LayoutOptions.value = options;
    resetErrors();
}

const EmailTemplateObject = computed(() => {
    let emailEvent = EmailEvents.value[EmailTemplateModel.value.ElementName];
    return emailEvent && emailEvent.templateObject ? emailEvent.templateObject : {};
});

onMounted(async () => {
    // updateEmailLayoutRef.value.statusLoading();
    // isLoading.value = true;
    await getEmailTemplateDetails();
    await getAllLanguages();
    await getLayoutOptionsByLanguage(true);
    await getEmailEvents();
    // updateEmailLayoutRef.value.statusNormal();
    // isLoading.value = false;
});

</script>

<template>
    <div class="content">
        <Loader :is-loading="isLoading"></Loader>
        <form v-if="!_.isEmpty(EmailTemplateModel)" @submit.prevent="updateEmailTemplate">

            <BaseBlock ref="updateEmailLayoutRef" content-full title="Edit Email Template">
                <template #options>
                    <router-link :to="{name:'email-templates'}" class="btn btn-sm btn-outline-info">
                        <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                    </router-link>
                </template>

                <div class="space-y-2">

                    <div class="row">
                        <div class="col-lg-4 space-y-2 ">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="ElementName">
                                    Element Name<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="ElementName"
                                        v-model="EmailTemplateModel.ElementName"
                                        :options="EmailEventOptions"
                                        :required="true"
                                        :select-class="errors.ElementName ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ElementName"
                                        @change="resetErrors"
                                    />
                                    <InputErrorMessages v-if="errors.ElementName"
                                                        :errorMessages="errors.ElementName"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 space-y-2">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="Language">
                                    Language<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="LanguageId"
                                        v-model="EmailTemplateModel.LanguageId"
                                        :options="LanguageOptions"
                                        :required="true"
                                        :select-class="errors.LanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Language"
                                        @change="getLayoutOptionsByLanguage"
                                    />
                                    <InputErrorMessages v-if="errors.LanguageId"
                                                        :errorMessages="errors.LanguageId"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 space-y-2">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="LayoutId">
                                    Layout<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="LayoutId"
                                        v-model="EmailTemplateModel.LayoutId"
                                        :options="LayoutOptions"
                                        :required="true"
                                        :select-class="errors.LayoutId ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="LayoutId"
                                        @change="resetErrors"
                                    />
                                    <InputErrorMessages v-if="errors.LayoutId"
                                                        :errorMessages="errors.LayoutId"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-lg-12 space-y-2">
                            <div class="row">
                                <label class="col-sm-1 col-form-label col-form-label-sm" for="Subject">
                                    Subject<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-11">
                                    <input
                                        id="Subject"
                                        v-model="EmailTemplateModel.Subject"
                                        :class="errors.Subject ? `is-invalid form-control-sm` : `form-control-sm`"
                                        autocomplete="off" class="form-control" name="Subject"
                                        placeholder="Subject"
                                        required
                                        type="text"
                                        @keyup="resetErrors"
                                    />
                                    <InputErrorMessages v-if="errors.Subject"
                                                        :errorMessages="errors.Subject"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


            </BaseBlock>

            <TemplateAndPreview
                :LanguageId="EmailTemplateModel.LanguageId"
                :LayoutId="EmailTemplateModel.LayoutId"
                :Subject="EmailTemplateModel.Subject"
                :Template="EmailTemplateModel.Template"
                :TemplateObject="EmailTemplateObject"
                :errors="errors"
                AppType="office"
                PageType="template"
                @setNewErrors="setNewErrors"
                @setTemplate="setTemplate"
            >
            </TemplateAndPreview>

            <button class="btn btn-outline-primary btn-sm col-2 mb-5" type="submit">Save</button>
        </form>
    </div>
</template>
