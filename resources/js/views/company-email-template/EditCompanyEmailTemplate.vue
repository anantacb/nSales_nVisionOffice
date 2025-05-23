<script setup>
import {computed, onMounted, ref} from "vue";
import _ from "lodash";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";
import CompanyEmailTemplate from "@/models/Company/CompanyEmailTemplate";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import TableHelper from "@/models/TableHelper";

const route = useRoute();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageOptions = ref([]);
let EmailEventOptions = ref([]);
let LayoutOptions = ref([]);
let CompanyEmailTemplateModel = ref({});
let EmailEvents = ref([]);
const updateEmailLayoutRef = ref(null);
const isLoading = ref(false);

let DatabaseTableOptions = ref([
    {
        label: 'Please Select',
        value: ''
    },
    {
        label: 'Orderhead',
        value: 'Orderhead'
    }
]);
let TableColumnOptions = ref([{
    label: 'Please Select',
    value: ''
}]);
let ColumnValueOptions = ref([
    {
        label: 'Please Select',
        value: ''
    }
]);

function databaseTableChanged() {
    CompanyEmailTemplateModel.value.TableColumn = '';
    TableColumnOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    CompanyEmailTemplateModel.value.ColumnValue = '';
    ColumnValueOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    if (!CompanyEmailTemplateModel.value.DatabaseTable) {
        return;
    }
    getTableColumns();
}

function tableColumnChanged() {
    CompanyEmailTemplateModel.value.ColumnValue = '';
    ColumnValueOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    if (!CompanyEmailTemplateModel.value.TableColumn) {
        return;
    }
    getColumnValues();
}

async function getTableColumns() {
    let {
        data,
        message
    } = await TableHelper.getAllColumns('Company', CompanyEmailTemplateModel.value.DatabaseTable, companyStore.selectedCompany.Id);
    data.forEach((column, index) => {
        TableColumnOptions.value.push({label: column, value: column});
    });
}

async function getColumnValues() {
    let {
        data,
        message
    } = await TableHelper.getColumnDistinctValues('Company', CompanyEmailTemplateModel.value.DatabaseTable, CompanyEmailTemplateModel.value.TableColumn, companyStore.selectedCompany.Id);
    data.forEach((column, index) => {
        ColumnValueOptions.value.push({label: column, value: column});
    });
}

const showDatabaseFormElements = computed(() => {
    return CompanyEmailTemplateModel.value.ElementName === 'ORDER_CONFIRMATION_MAIL';
});

function setTemplate(newEditorValue) {
    CompanyEmailTemplateModel.value.Template = newEditorValue;
    resetErrors();
}

function setNewErrors(newErrors) {
    setErrors(newErrors);
}

async function updateEmailTemplate() {
    isLoading.value = true;
    let formData = {
        Id: CompanyEmailTemplateModel.value.Id,
        ElementName: CompanyEmailTemplateModel.value.ElementName,
        LayoutId: CompanyEmailTemplateModel.value.LayoutId,
        LanguageId: CompanyEmailTemplateModel.value.LanguageId,
        Subject: CompanyEmailTemplateModel.value.Subject,
        Template: CompanyEmailTemplateModel.value.Template,
        DatabaseTable: CompanyEmailTemplateModel.value.DatabaseTable,
        TableColumn: CompanyEmailTemplateModel.value.TableColumn,
        ColumnValue: CompanyEmailTemplateModel.value.ColumnValue,
    };

    try {
        let {data, message} = await CompanyEmailTemplate.update(companyStore.selectedCompany.Id, formData);
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
    let {data} = await CompanyEmailTemplate.details(companyStore.selectedCompany.Id, route.params.id);
    CompanyEmailTemplateModel.value = data;
    if (CompanyEmailTemplateModel.value.DatabaseTable) {
        await getTableColumns();
        await getColumnValues();
    }
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

async function getEmailEvents() {
    isLoading.value = true;
    let {data} = await CompanyEmailTemplate.getEmailEvents(companyStore.selectedCompany.Id);
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
        CompanyEmailTemplateModel.value.LayoutId = '';
    }

    let {data} = await CompanyEmailLayout.getLayoutOptionsByLanguage(companyStore.selectedCompany.Id, CompanyEmailTemplateModel.value.LanguageId);
    let options = [{label: 'Select Layout', value: ''}];
    data.forEach((layout) => {
        let option = {label: layout.Name, value: layout.Id};
        options.push(option);
    });
    LayoutOptions.value = options;
    resetErrors();
}

const EmailTemplateObject = computed(() => {
    let emailEvent = EmailEvents.value[CompanyEmailTemplateModel.value.ElementName];
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
        <form v-if="!_.isEmpty(CompanyEmailTemplateModel)" @submit.prevent="updateEmailTemplate">

            <BaseBlock ref="updateEmailLayoutRef" content-full title="Edit Email Template">
                <template #options>
                    <router-link :to="{name:'company-email-templates'}" class="btn btn-sm btn-outline-info">
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
                                        v-model="CompanyEmailTemplateModel.ElementName"
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
                                        v-model="CompanyEmailTemplateModel.LanguageId"
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
                                        v-model="CompanyEmailTemplateModel.LayoutId"
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


                    <div v-if="showDatabaseFormElements" class="row">
                        <div class="col-lg-4 space-y-2 ">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="DatabaseTable">
                                    Table
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="ElementName"
                                        v-model="CompanyEmailTemplateModel.DatabaseTable"
                                        :options="DatabaseTableOptions"
                                        :required="false"
                                        :select-class="errors.DatabaseTable ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ElementName"
                                        @change="resetErrors();databaseTableChanged()"
                                    />
                                    <InputErrorMessages v-if="errors.DatabaseTable"
                                                        :errorMessages="errors.DatabaseTable"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 space-y-2">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="Column">
                                    Column<span v-if="!!CompanyEmailTemplateModel.DatabaseTable"
                                                class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="LanguageId"
                                        v-model="CompanyEmailTemplateModel.TableColumn"
                                        :options="TableColumnOptions"
                                        :required="!!CompanyEmailTemplateModel.DatabaseTable"
                                        :select-class="errors.TableColumn ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Language"
                                        @change="tableColumnChanged()"
                                    />
                                    <InputErrorMessages v-if="errors.TableColumn"
                                                        :errorMessages="errors.TableColumn"></InputErrorMessages>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 space-y-2">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="ColumnValue">
                                    Value<span v-if="!!CompanyEmailTemplateModel.DatabaseTable"
                                               class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="ColumnValue"
                                        v-model="CompanyEmailTemplateModel.ColumnValue"
                                        :options="ColumnValueOptions"
                                        :required="!!CompanyEmailTemplateModel.DatabaseTable"
                                        :select-class="errors.ColumnValue ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ColumnValue"
                                        @change="resetErrors"
                                    />
                                    <InputErrorMessages v-if="errors.ColumnValue"
                                                        :errorMessages="errors.ColumnValue"></InputErrorMessages>
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
                                        v-model="CompanyEmailTemplateModel.Subject"
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
                :LanguageId="CompanyEmailTemplateModel.LanguageId"
                :LayoutId="CompanyEmailTemplateModel.LayoutId"
                :Subject="CompanyEmailTemplateModel.Subject"
                :Template="CompanyEmailTemplateModel.Template"
                :TemplateObject="EmailTemplateObject"
                :errors="errors"
                AppType="company"
                PageType="template"
                @setNewErrors="setNewErrors"
                @setTemplate="setTemplate"
            >
            </TemplateAndPreview>

            <button class="btn btn-outline-primary btn-sm col-2 mb-5" type="submit">Save</button>
        </form>
    </div>
</template>
