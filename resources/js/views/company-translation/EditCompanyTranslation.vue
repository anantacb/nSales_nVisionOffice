<script setup>
import {onMounted, ref, watch} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Translation from "@/models/Office/Translation";
import JsonEditorVue from "json-editor-vue";
import TableHelper from "@/models/TableHelper";
import {useTemplateStore} from "@/stores/templateStore";
import CompanyTranslation from "@/models/Company/CompanyTranslation";
import {useCompanyStore} from "@/stores/companyStore";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import router from "@/router";

const route = useRoute();
const notificationStore = useNotificationStore();
const templateStore = useTemplateStore();
const companyStore = useCompanyStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let CompanyTranslationModel = ref({});

const updateCompanyTranslationRef = ref(null);

async function updateCompanyTranslation() {
    updateCompanyTranslationRef.value.statusLoading();
    let translations = {};
    if (typeof CompanyTranslationModel.value.Translations === 'object') {
        translations = CompanyTranslationModel.value.Translations;
    } else if (typeof CompanyTranslationModel.value.Translations === 'string') {
        if (CompanyTranslationModel.value.Translations) {
            translations = JSON.parse(CompanyTranslationModel.value.Translations);
        }
    }
    let formData = {
        Id: CompanyTranslationModel.value.Id,
        CompanyLanguageId: CompanyTranslationModel.value.CompanyLanguageId,
        Type: CompanyTranslationModel.value.Type,
        ElementName: CompanyTranslationModel.value.ElementName,
        Translations: translations
    };

    try {
        let {data, message} = await CompanyTranslation.update(companyStore.selectedCompany.Id, formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateCompanyTranslationRef.value.statusNormal();
}

async function getCompanyTranslationDetails() {
    let {data} = await CompanyTranslation.details(companyStore.selectedCompany.Id, route.params.id);
    CompanyTranslationModel.value = data;
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

onMounted(async () => {
    updateCompanyTranslationRef.value.statusLoading();
    await getCompanyTranslationDetails();
    await getTypes();
    await getAllCompanyLanguages()
    updateCompanyTranslationRef.value.statusNormal();
});

watch(() => companyStore.selectedCompany, (value, oldValue) => {
    if (value.Id !== oldValue.Id) {
        router.push({name: 'home'});
    }
});


</script>

<template>
    <div class="content">

        <BaseBlock ref="updateCompanyTranslationRef" content-full title="Edit Translation">

            <template #options>
                <router-link :to="{name:'company-translations'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateCompanyTranslation">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Language" v-model="CompanyTranslationModel.CompanyLanguageId"
                                        :options="CompanyLanguageOptions"
                                        :required="true"
                                        :select-class="errors.CompanyLanguage ? `is-invalid form-select-sm` : `form-select-sm`"
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
                                <Select id="Type" v-model="CompanyTranslationModel.Type" :options="TypeOptions"
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
                                <input id="ElementName" v-model="CompanyTranslationModel.ElementName"
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
                                    v-model="CompanyTranslationModel.Translations"
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
