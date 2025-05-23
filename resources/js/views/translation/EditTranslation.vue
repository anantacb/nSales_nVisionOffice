<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Translation from "@/models/Office/Translation";
import JsonEditorVue from "json-editor-vue";
import TableHelper from "@/models/TableHelper";
import Language from "@/models/Office/Language";
import {useTemplateStore} from "@/stores/templateStore";

const route = useRoute();
const notificationStore = useNotificationStore();
const templateStore = useTemplateStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let TranslationModel = ref({});

const updateTranslationRef = ref(null);

async function updateTranslation() {
    updateTranslationRef.value.statusLoading();
    let translations = {};
    if (typeof TranslationModel.value.Translations === 'object') {
        translations = TranslationModel.value.Translations;
    } else if (typeof TranslationModel.value.Translations === 'string') {
        if (TranslationModel.value.Translations) {
            translations = JSON.parse(TranslationModel.value.Translations);
        }
    }
    let formData = {
        Id: TranslationModel.value.Id,
        LanguageId: TranslationModel.value.LanguageId,
        Type: TranslationModel.value.Type,
        ElementName: TranslationModel.value.ElementName,
        Translations: translations
    };

    try {
        let {data, message} = await Translation.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateTranslationRef.value.statusNormal();
}

async function getTranslationDetails() {
    let {data} = await Translation.details(route.params.id);
    TranslationModel.value = data;
}

let TypeOptions = ref([]);

async function getTypes() {
    let {data: TypeData} = await TableHelper.getEnumValues('Office', `Translation`, 'Type');
    TypeOptions.value = TypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

let LanguageOptions = ref([]);

async function getAllLanguages() {
    const {data} = await Language.getAllLanguages();
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    LanguageOptions.value = options;
}

onMounted(async () => {
    updateTranslationRef.value.statusLoading();
    await getTranslationDetails();
    await getTypes();
    await getAllLanguages()
    updateTranslationRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="updateTranslationRef" content-full title="Edit Translation">
            <template #options>
                <router-link :to="{name:'translations'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="updateTranslation">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Language" v-model="TranslationModel.LanguageId" :options="LanguageOptions"
                                        :required="true"
                                        :select-class="errors.CompanyLanguage ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Language"
                                />
                                <InputErrorMessages v-if="errors.LanguageId"
                                                    :errorMessages="errors.LanguageId"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="TranslationModel.Type" :options="TypeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Type"
                                />
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>


                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ElementName">
                                ElementName<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="ElementName" v-model="TranslationModel.ElementName"
                                       :class="errors.ElementName ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="ElementName"
                                       placeholder="ElementName"
                                       required
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.ElementName"
                                                    :errorMessages="errors.ElementName"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 space-y-2">
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Translations">
                                Translations<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <JsonEditorVue
                                    v-model="TranslationModel.Translations"
                                    :class="{
                                     'jse-theme-dark' : templateStore.settings.darkMode,
                                     'is-invalid' : errors.Translations
                                    }"
                                    :main-menu-bar="false"
                                    :status-bar="false"
                                    mode="text"
                                >
                                </JsonEditorVue>
                                <InputErrorMessages v-if="errors.Translations"
                                                    :errorMessages="errors.Translations"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>
        </BaseBlock>

    </div>
</template>
