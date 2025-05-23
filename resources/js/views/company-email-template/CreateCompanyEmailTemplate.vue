<script setup>
import {computed, nextTick, onMounted, ref} from "vue";
import router from "@/router";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import TemplateAndPreview from "@/components/email/TemplateAndPreview.vue";
import Loader from "@/components/ui/Loader/Loader.vue";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import CompanyEmailTemplate from "@/models/Company/CompanyEmailTemplate";
import TableHelper from "@/models/TableHelper";

const companyStore = useCompanyStore();
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

let DatabaseTable = ref('');
let TableColumn = ref('');
let ColumnValue = ref('');

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
    TableColumn.value = '';
    TableColumnOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    ColumnValue.value = '';
    ColumnValueOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    if (!DatabaseTable.value) {
        return;
    }
    getTableColumns();
}

function tableColumnChanged() {
    ColumnValue.value = '';
    ColumnValueOptions.value = [{
        label: 'Please Select',
        value: ''
    }];
    if (!TableColumn.value) {
        return;
    }
    getColumnValues();
}

async function getTableColumns() {
    let {
        data,
        message
    } = await TableHelper.getAllColumns('Company', DatabaseTable.value, companyStore.selectedCompany.Id);
    data.forEach((column, index) => {
        TableColumnOptions.value.push({label: column, value: column});
    });
}

async function getColumnValues() {
    let {
        data,
        message
    } = await TableHelper.getColumnDistinctValues('Company', DatabaseTable.value, TableColumn.value, companyStore.selectedCompany.Id);
    data.forEach((column, index) => {
        ColumnValueOptions.value.push({label: column, value: column});
    });
}

const showDatabaseFormElements = computed(() => {
    return ElementName.value === 'ORDER_CONFIRMATION_MAIL';
});

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

        DatabaseTable: DatabaseTable.value,
        TableColumn: TableColumn.value,
        ColumnValue: ColumnValue.value,

        Subject: Subject.value,
        Template: Template.value,
    };

    try {
        let {data, message} = await CompanyEmailTemplate.create(companyStore.selectedCompany.Id, formData);
        isLoading.value = false;
        notificationStore.showNotification(message);

        await nextTick();
        await router.push({name: 'company-email-templates'});
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
        LayoutId.value = '';
    }

    let options = [{label: 'Select Layout', value: ''}];

    if (!LanguageId.value) {
        LayoutOptions.value = options;
        return;
    }

    let {data} = await CompanyEmailLayout.getLayoutOptionsByLanguage(companyStore.selectedCompany.Id, LanguageId.value);
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
            <BaseBlock ref="createEmailLayoutRef" content-full title="Create Email Template">
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

                    <div v-if="showDatabaseFormElements" class="row">
                        <div class="col-lg-4 space-y-2 ">
                            <div class="row">
                                <label class="col-sm-3 col-form-label col-form-label-sm" for="DatabaseTable">
                                    Table
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="ElementName"
                                        v-model="DatabaseTable"
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
                                    Column<span v-if="!!DatabaseTable" class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="LanguageId"
                                        v-model="TableColumn"
                                        :options="TableColumnOptions"
                                        :required="!!DatabaseTable"
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
                                    Value<span v-if="!!DatabaseTable" class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <Select
                                        id="ColumnValue"
                                        v-model="ColumnValue"
                                        :options="ColumnValueOptions"
                                        :required="!!DatabaseTable"
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
