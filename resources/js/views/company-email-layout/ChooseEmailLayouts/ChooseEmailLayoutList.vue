<script setup>
import {nextTick, onMounted, ref, watch} from 'vue';
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import useGridManagement from "@/composables/useGridManagement";
import {useFormErrors} from "@/composables/useFormErrors";
import Loader from "@/components/ui/Loader/Loader.vue";
import router from "@/router";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import EmailLayout from "@/models/Office/EmailLayout";
import _ from "lodash";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const isLoading = ref(false);
let tableData = ref([]);
let paginationData = ref(null);
let previewLayoutRef = ref(null);

const PreviewTemplateObject = ref([]);
const CompanyLanguages = ref([]);
const CompanyLanguageOptions = ref([]);
const previewTemplate = ref('');
const SelectedEmailLayout = ref({});

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
        name: "Name",
        title: "Name",
        sortField: "Name"
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
        name: "Action",
        title: "Action"
    }
]);

setSearchColumns(['Name']);

function goToPage(pageNo) {
    setPageNo(pageNo);
    getEmailLayoutsForCompany();
}

function sortBy({field, order}) {
    setSortBy(field, order);
    setPageNo(1);
    getEmailLayoutsForCompany();
}

function search(query) {
    setSearchQuery(query);
    setPageNo(1);
    getEmailLayoutsForCompany();
}

async function getEmailLayoutsForCompany() {
    let {
        data,
        pagination
    } = await EmailLayout.getEmailLayoutsForCompany(companyStore.selectedCompany.Id, request.value);
    tableData.value = data;
    paginationData.value = pagination;
}

async function getPreviewTemplateObjectForLayout() {
    isLoading.value = true;
    let {data} = await EmailLayout.getPreviewTemplateObjectForLayout();
    PreviewTemplateObject.value = data;
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

async function showPreviewModal(emailLayout) {
    isLoading.value = true;
    // console.log(emailLayout);
    await getDataForPreview(emailLayout);
    previewLayoutRef.value.openModal();
    isLoading.value = false;
}

function resetPreview() {
    previewTemplate.value = '';
}

async function getDataForPreview(emailLayout) {
    resetPreview();
    const formData = {
        LanguageId: emailLayout.LanguageId,
        Template: emailLayout.Template,
        TemplateObject: PreviewTemplateObject.value,
    };

    let {data} = await EmailLayout.getDataForPreview(formData);
    previewTemplate.value = data.template;

    if (!previewTemplate.value) {
        notificationStore.showNotification("Unable to preview", "error");
    }

}

async function setTemplateLanguageId() {
    const matchedLanguage = CompanyLanguages.value.find(
        lang => lang.Code === SelectedEmailLayout.value.language.Code
    );

    if (matchedLanguage) {
        SelectedEmailLayout.value.LanguageId = matchedLanguage.Id;
    } else {
        const defaultLanguage = CompanyLanguages.value.find(lang => lang.IsDefault === 1);
        SelectedEmailLayout.value.LanguageId = defaultLanguage.Id;
    }
}

async function copyEmailLayoutToCompany(selectedEmailLayout) {
    resetErrors();
    SelectedEmailLayout.value = selectedEmailLayout;
    await setTemplateLanguageId();
    let formData = {
        Name: SelectedEmailLayout.value.Name,
        LanguageId: SelectedEmailLayout.value.LanguageId,
        Template: SelectedEmailLayout.value.Template,
    };

    isLoading.value = true;
    try {
        let {
            data,
            message
        } = await CompanyEmailLayout.copyLayoutToCompany(companyStore.selectedCompany.Id, formData);

        notificationStore.showNotification(message);
        await nextTick();
        await router.push({name: 'company-email-layouts'});

    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
            if (errors.value) {
                Object.entries(errors.value).forEach(error => {
                    error[1].forEach(err => notificationStore.showNotification(err, "error"));
                });
            }
        }
    } finally {
        isLoading.value = false;
    }

}

onMounted(async () => {
    await getEmailLayoutsForCompany();
    await getPreviewTemplateObjectForLayout();
    await getAllCompanyLanguages();
});

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        await getEmailLayoutsForCompany();
        await getPreviewTemplateObjectForLayout();
        await getAllCompanyLanguages();
    }
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
                    @click="copyEmailLayoutToCompany(props.data)">
                <i class="fa fa-copy"></i>
            </button>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="showPreviewModal(props.data)">
                <i class="fa fa-eye"></i>
            </button>
        </template>
    </DataGrid>

    <!-- Modal for previewing email template -->
    <ModalComponent id="previewTemplate" ref="previewLayoutRef" modal-body-classes="modal-xl">
        <template v-slot:modal-content>
            <BaseBlock class="mb-0" title="Preview Layout" transparent>
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

</template>

<style scoped>
.preview-iframe {
    border: 1px solid #2a384b;;
    width: 100%;
    height: 65vh;
}

</style>
