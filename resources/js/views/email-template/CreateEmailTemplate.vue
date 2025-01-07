<script setup>
import {onMounted, ref, nextTick, computed} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import EmailLayout from "@/models/Office/EmailLayout";
import Language from "@/models/Office/Language";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";
import EmailTemplate from "@/models/Office/EmailTemplate";

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const createEmailLayoutRef = ref(null);
let LanguageOptions = ref([]);
let LayoutOptions = ref([{label: 'Select Layout', value: ''}]);
let EmailEventOptions = ref([]);
let EmailEvents = ref([]);
const isLoading = ref(false)

let ElementName = ref('');
let LanguageId = ref('');
let LayoutId = ref('');
let Template = ref('');
let Subject = ref('');

function setTemplate(newEditorValue) {
    Template.value = newEditorValue;
    resetErrors();
}

function setNewErrors(newErrors) {
    setErrors(newErrors);
}

async function createEmailTemplate() {
    isLoading.value = true;
    let formData = {
        ElementName: ElementName.value,
        LanguageId: LanguageId.value,
        LayoutId: LayoutId.value,
        Subject: Subject.value,
        Template: Template.value,
    };

    try {
        let {data, message} = await EmailTemplate.create(formData);
        isLoading.value = false;
        notificationStore.showNotification(message);

        await nextTick();
        await router.push({name: 'email-templates'});
    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        isLoading.value = false;
    }

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
        LayoutId.value = '';
    }

    let options = [{label: 'Select Layout', value: ''}];

    if (!LanguageId.value) {
        LayoutOptions.value = options;
        return;
    }

    let {data} = await EmailLayout.getLayoutOptionsByLanguage(LanguageId.value);
    data.forEach((layout) => {
        let option = {label: layout.Name, value: layout.Id};
        options.push(option);
    });
    LayoutOptions.value = options;
    resetErrors();
}

const EmailTemplateObject = computed(() => {
    let emailEvent = EmailEvents.value[ElementName.value];
    return emailEvent && emailEvent.templateObject ? emailEvent.templateObject : {};
});

onMounted(async () => {
    await getAllLanguages();
    // await getLayoutOptionsByLanguage(true);
    await getEmailEvents();
});
</script>

<template>
    <div class="content">
        <Loader :is-loading="isLoading"></Loader>

        <form class="space-y-4" @submit.prevent="createEmailTemplate">
            <BaseBlock ref="createEmailLayoutRef" content-full title="Create Email layout">
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
                                        v-model="ElementName"
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
                                        v-model="LanguageId"
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
                                        v-model="LayoutId"
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
                                        v-model="Subject"
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
                :LanguageId="LanguageId"
                :LayoutId="LayoutId"
                :Subject="Subject"
                :Template="Template"
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
