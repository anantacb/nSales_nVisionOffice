<script setup>
import {onMounted, ref, watch} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import TableHelper from "@/models/TableHelper";
import JsonEditorVue from "json-editor-vue";
import {useTemplateStore} from "@/stores/templateStore";
import CompanyTranslation from "@/models/Company/CompanyTranslation";
import {useCompanyStore} from "@/stores/companyStore";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import useCompanyInfos from "@/composables/useCompanyInfos";

const notificationStore = useNotificationStore();
const templateStore = useTemplateStore();
const companyStore = useCompanyStore();
let {errors, setErrors, resetErrors} = useFormErrors();
let {isModuleEnabled} = useCompanyInfos();

watch(() => companyStore.selectedCompanyModules, () => {
    if (!isModuleEnabled('Translation')) {
        router.push({name: 'home'});
        notificationStore.showNotification('Module Not Enabled.', 'error', 15000);
    }
});

let Type = ref('WebPage');
let ElementName = ref('');
let Translations = ref({});
let CompanyLanguageId = ref(null);

const createCompanyTranslationRef = ref(null);

onMounted(async () => {
    createCompanyTranslationRef.value.statusLoading();
    await getTypes();
    await getAllCompanyLanguages();
    createCompanyTranslationRef.value.statusNormal();
});

let TypeOptions = ref([]);

async function getTypes() {
    let {data: TypeData} = await TableHelper.getEnumValues('Company', `CompanyTranslation`, 'Type', companyStore.selectedCompany.Id);
    TypeOptions.value = TypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

let CompanyLanguageOptions = ref([]);

async function getAllCompanyLanguages() {
    const {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id);
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    CompanyLanguageOptions.value = options;
}

async function createCompanyTranslation() {
    createCompanyTranslationRef.value.statusLoading();
    let translations = {};
    if (typeof Translations.value === 'object') {
        translations = Translations.value;
    } else if (typeof Translations.value === 'string') {
        if (Translations.value) {
            translations = JSON.parse(Translations.value);
        }
    }

    let formData = {
        CompanyLanguageId: CompanyLanguageId.value,
        Type: Type.value,
        ElementName: ElementName.value,
        Translations: translations,
    };
    try {
        let {data, message} = await CompanyTranslation.create(companyStore.selectedCompany.Id, formData);
        await router.push({name: 'company-translations'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createCompanyTranslationRef.value.statusNormal();
    }
}

</script>

<template>
    <div class="content">

        <BaseBlock ref="createCompanyTranslationRef" content-full title="Create Translation">

            <template #options>
                <router-link :to="{name:'company-translations'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createCompanyTranslation">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Language" v-model="CompanyLanguageId" :options="CompanyLanguageOptions"
                                        :required="true"
                                        :select-class="errors.CompanyLanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Language"
                                />
                                <InputErrorMessages v-if="errors.CompanyLanguageId"
                                                    :errorMessages="errors.CompanyLanguageId"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="Type" :options="TypeOptions"
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
                                <input id="ElementName" v-model="ElementName"
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
                                    v-model="Translations"
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
