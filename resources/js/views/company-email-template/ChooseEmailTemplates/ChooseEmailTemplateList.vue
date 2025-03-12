<script setup>
import {nextTick, onMounted, ref} from 'vue';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import useGridManagement from "@/composables/useGridManagement";
import {useFormErrors} from "@/composables/useFormErrors";
import Loader from "@/components/ui/Loader/Loader.vue";
import router from "@/router";
import EmailTemplate from "@/models/Office/EmailTemplate";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import CompanyEmailTemplate from "@/models/Company/CompanyEmailTemplate";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import CompanyLanguage from "@/models/Company/CompanyLanguage";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const isLoading = ref(false);
let tableData = ref([]);
let paginationData = ref(null);
let previewTemplateRef = ref(null);
let copyTemplateRef = ref(null);

const EmailEventOptions = ref([]);
const EmailEvents = ref([]);
const CompanyEmailEventOptions = ref([]);
const CompanyEmailEvents = ref([]);
const CompanyLayoutOptions = ref([]);
const CompanyLanguages = ref([]);
const CompanyLanguageOptions = ref([]);
const previewTemplate = ref('');
const previewSubject = ref('');
const SelectedEmailTemplate = ref({});

const {
    tableFields,
    bodyHeight,
    request,
    setTableFields,
    setSearchColumns,
    setSearchQuery,
    setPageNo,
    setSortBy
} = useGridManagement();

setTableFields([
    {
        name: "ModifiedElementName",
        title: "Element Name",
        sortField: "ElementName"
    },
    {
        name: "language",
        title: "Language",
        formatter: (language) => {
            return language ? language.Name : '';
        },
        sortField: "LanguageId"
    },
    {
        name: "email_layout",
        title: "Layout",
        formatter: (email_layout) => {
            return email_layout ? email_layout.Name : '';
        },
        sortField: "LayoutId"
    },
    {
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(['Name']);

function goToPage(pageNo) {
    setPageNo(pageNo);
    getEmailTemplatesForCompany();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getEmailTemplatesForCompany();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getEmailTemplatesForCompany();
}

async function getEmailTemplatesForCompany() {
    let {
        data,
        pagination
    } = await EmailTemplate.getEmailTemplatesForCompany(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
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

async function getCompanyEmailEvents() {
    isLoading.value = true;
    let {data} = await CompanyEmailTemplate.getEmailEvents(companyStore.selectedCompany.Id);
    CompanyEmailEvents.value = data;
    let options = [{label: 'Select Element Name', value: ''}];

    Object.entries(CompanyEmailEvents.value).forEach(([emailEventKey, emailEvent]) => {
        let option = {label: emailEvent.Title, value: emailEventKey};
        options.push(option);
    });

    CompanyEmailEventOptions.value = options;
    isLoading.value = false;
}

async function getAllCompanyLanguages() {
    isLoading.value = true;
    const {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id);
    CompanyLanguages.value = data;
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    CompanyLanguageOptions.value = options;
    isLoading.value = false;
}

async function getLayoutOptionsByLanguage(initialLoad = false) {
    if (!initialLoad) {
        SelectedEmailTemplate.value.LayoutId = '';
        isLoading.value = true;
    }

    let {data} = await CompanyEmailLayout.getLayoutOptionsByLanguage(companyStore.selectedCompany.Id, SelectedEmailTemplate.value.LanguageId);
    let options = [{label: 'Select Layout', value: ''}];
    data.forEach((layout) => {
        let option = {label: layout.Name, value: layout.Id};
        options.push(option);
    });
    CompanyLayoutOptions.value = options;
    resetErrors();
    isLoading.value = false;
}

async function setTemplateLanguageId() {
    // console.log(CompanyLanguages.value);
    const matchedLanguage = CompanyLanguages.value.find(
        lang => lang.Code === SelectedEmailTemplate.value.language.Code
    );

    if (matchedLanguage) {
        SelectedEmailTemplate.value.LanguageId = matchedLanguage.Id;
    } else {
        const defaultLanguage = CompanyLanguages.value.find(lang => lang.IsDefault === 1);
        SelectedEmailTemplate.value.LanguageId = defaultLanguage.Id;
    }
}

async function showPreviewModal(emailTemplate) {
    isLoading.value = true;
    await getDataForPreview(emailTemplate);
    previewTemplateRef.value.openModal();
    isLoading.value = false;
}

async function showCopyModal(emailTemplate) {
    isLoading.value = true;
    resetErrors();
    SelectedEmailTemplate.value = emailTemplate;

    if (!(SelectedEmailTemplate.value.ElementName in CompanyEmailEvents.value)) {
        SelectedEmailTemplate.value.ElementName = '';
    }

    await setTemplateLanguageId();
    await getLayoutOptionsByLanguage(true);
    isLoading.value = false;
    copyTemplateRef.value.openModal();
}

function resetPreview() {
    previewSubject.value = '';
    previewTemplate.value = '';
}

async function getDataForPreview(emailTemplate) {
    resetPreview();
    const formData = {
        LanguageId: emailTemplate.LanguageId,
        Template: emailTemplate.Template,
        TemplateObject: EmailEvents.value[emailTemplate.ElementName].templateObject,
        LayoutId: emailTemplate.LayoutId,
        Subject: emailTemplate.Subject,
    };

    let {data} = await EmailTemplate.getDataForPreview(formData);
    previewTemplate.value = data.template;
    previewSubject.value = data.subject;

    if (!previewTemplate.value) {
        notificationStore.showNotification("Unable to preview", "error");
    }

}

async function copyEmailTemplateToCompany() {
    isLoading.value = true;
    let formData = {
        ElementName: SelectedEmailTemplate.value.ElementName,
        LanguageId: SelectedEmailTemplate.value.LanguageId,
        LayoutId: SelectedEmailTemplate.value.LayoutId,
        Subject: SelectedEmailTemplate.value.Subject,
        Template: SelectedEmailTemplate.value.Template,
    };

    try {
        let {
            data,
            message
        } = await CompanyEmailTemplate.copyTemplateToCompany(companyStore.selectedCompany.Id, formData);

        notificationStore.showNotification(message);
        copyTemplateRef.value.closeModal();
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

onMounted(async () => {
    await getEmailTemplatesForCompany();
    await getEmailEvents();
    await getCompanyEmailEvents();
    await getAllCompanyLanguages();
});


</script>

<template>
    <Loader :is-loading="isLoading"></Loader>
    <DataGrid
        :expandable="false"
        :height="bodyHeight"
        :isLoading="isLoading"
        :pagination="paginationData"
        :searchString="request.query"
        :searchable="true"
        :tableData="tableData"
        :tableFields="tableFields"
        @expand=""
        @paginate="goToPage"
        @search="search"
        @sortBy="sortBy"
    >
        <template v-slot:body-Action="props">
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="showCopyModal(props.data)">
                <i class="fa fa-copy"></i>
            </button>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="showPreviewModal(props.data)">
                <i class="fa fa-eye"></i>
            </button>
        </template>
    </DataGrid>

    <!-- Modal for previewing email template -->
    <ModalComponent id="previewTemplate" ref="previewTemplateRef" modal-body-classes="modal-xl">
        <template v-slot:modal-content>
            <BaseBlock class="mb-0" title="Copy Template" transparent>
                <template #options>
                    <button
                        aria-label="Close"
                        class="btn-block-option"
                        data-bs-dismiss="modal"
                        type="button"
                    >
                        <i class="fa fa-fw fa-times"></i>
                    </button>
                </template>

                <template #content>
                    <div class="block-content tab-content fs-sm scrollable-modal-content">
                        <!-- Content goes here -->
                        <div class="row fs-sm">
                            <div class="col-12 mb-2">
                                <span>Subject: </span>
                                <span v-html="previewSubject"></span>
                            </div>

                            <div class="col-12">
                                <span>Body: </span>
                                <iframe :srcdoc="previewTemplate" class="preview-iframe"></iframe>
                            </div>
                        </div>

                    </div>
                    <div class="block-content block-content-full text-end bg-body">
                        <button class="btn btn-sm btn-outline-danger me-1" data-bs-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </template>
            </BaseBlock>
        </template>
    </ModalComponent>

    <!-- modal for copyTemplate -->
    <ModalComponent id="copyTemplate" ref="copyTemplateRef" modal-body-classes="modal-xl">
        <template v-slot:modal-content>
            <BaseBlock class="mb-0" title="Copy Template" transparent>
                <template #options>
                    <button
                        aria-label="Close"
                        class="btn-block-option"
                        data-bs-dismiss="modal"
                        type="button"
                    >
                        <i class="fa fa-fw fa-times"></i>
                    </button>
                </template>

                <template #content>
                    <div class="block-content tab-content fs-sm scrollable-modal-content mb-5">
                        <Loader :is-loading="isLoading"></Loader>

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
                                                v-model="SelectedEmailTemplate.ElementName"
                                                :options="CompanyEmailEventOptions"
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
                                                v-model="SelectedEmailTemplate.LanguageId"
                                                :options="CompanyLanguageOptions"
                                                :required="true"
                                                :select-class="errors.LanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="Language"
                                                @change="getLayoutOptionsByLanguage(false)"
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
                                                v-model="SelectedEmailTemplate.LayoutId"
                                                :options="CompanyLayoutOptions"
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

                                <div class="col-lg-12 space-y-2">
                                    <div class="row">
                                        <label class="col-sm-1 col-form-label col-form-label-sm" for="Subject">
                                            Subject<span class="text-danger">*</span>
                                        </label>
                                        <div class="col-sm-11">
                                            <input
                                                id="Subject"
                                                v-model="SelectedEmailTemplate.Subject"
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

                    </div>

                    <div class="block-content block-content-full text-end bg-body">
                        <button class="btn btn-sm btn-outline-danger me-1" data-bs-dismiss="modal" type="button">
                            Cancel
                        </button>
                        <button class="btn btn-sm btn-outline-success" type="button"
                                @click="copyEmailTemplateToCompany">
                            Copy Template
                        </button>
                    </div>
                </template>
            </BaseBlock>
        </template>
    </ModalComponent>
</template>

<style scoped>
.preview-iframe {
    border: 1px solid #2a384b;;
    width: 100%;
    height: 65vh;
}

</style>
