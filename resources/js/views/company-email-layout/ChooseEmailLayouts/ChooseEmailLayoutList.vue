<script setup>
import {nextTick, onMounted, ref} from 'vue';
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

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const isLoading = ref(false);
let tableData = ref([]);
let paginationData = ref(null);
let previewTemplateRef = ref(null);
let copyLayoutRef = ref(null);

const PreviewTemplateObject = ref([]);
const CompanyPreviewTemplateObject = ref([]);
const CompanyLanguages = ref([]);
const CompanyLanguageOptions = ref([]);
const previewTemplate = ref('');
const previewSubject = ref('');
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

async function getCompanyPreviewTemplateObjectForLayout() {
    isLoading.value = true;
    let {data} = await CompanyEmailLayout.getPreviewTemplateObjectForLayout(companyStore.selectedCompany.Id);
    CompanyPreviewTemplateObject.value = data;
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
    console.log(emailLayout);
    await getDataForPreview(emailLayout);
    previewTemplateRef.value.openModal();
    isLoading.value = false;
}

async function showCopyModal(emailLayout) {
    isLoading.value = true;
    resetErrors();
    SelectedEmailLayout.value = emailLayout;
    isLoading.value = false;
    copyLayoutRef.value.openModal();
}

function resetPreview() {
    previewSubject.value = '';
    previewTemplate.value = '';
}

async function getDataForPreview(emailLayout) {
    resetPreview();
    const formData = {
        LanguageId: emailLayout.LanguageId,
        Template: emailLayout.Template,
        TemplateObject: PreviewTemplateObject.value,
        // LayoutId: emailLayout.LayoutId,
        // Subject: emailLayout.Subject,
    };

    let {data} = await EmailLayout.getDataForPreview(formData);
    previewTemplate.value = data.template;
    previewSubject.value = data.subject;

    if (!previewTemplate.value) {
        notificationStore.showNotification("Unable to preview", "error");
    }

}

async function copyEmailLayoutToCompany() {
    isLoading.value = true;
    let formData = {
        Name: SelectedEmailLayout.value.Name,
        LanguageId: SelectedEmailLayout.value.LanguageId,
        Template: SelectedEmailLayout.value.Template,
    };

    try {
        let {
            data,
            message
        } = await CompanyEmailLayout.copyLayoutToCompany(companyStore.selectedCompany.Id, formData);

        notificationStore.showNotification(message);
        copyLayoutRef.value.closeModal();
        await nextTick();
        await router.push({name: 'company-email-layouts'});
    } catch (error) {
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
            if (errors.value.Name && errors.value.Name.length) {
                errors.value.Name.forEach(error => notificationStore.showNotification(error, "error"));
            }
        }
    } finally {
        isLoading.value = false;
    }

}

onMounted(async () => {
    await getEmailLayoutsForCompany();
    await getPreviewTemplateObjectForLayout();
    await getCompanyPreviewTemplateObjectForLayout();
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
                            <!--                            <div class="col-12 mb-2">-->
                            <!--                                <span>Subject: </span>-->
                            <!--                                <span v-html="previewSubject"></span>-->
                            <!--                            </div>-->

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
    <ModalComponent id="copyTemplate" ref="copyLayoutRef" modal-body-classes="modal-xl">
        <template v-slot:modal-content>
            <BaseBlock class="mb-0" title="Copy Layout" transparent>
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
                                <div class="col-lg-6 space-y-2">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label col-form-label-sm" for="Language">
                                            Language<span class="text-danger">*</span>
                                        </label>
                                        <div class="col-sm-9">
                                            <Select
                                                id="LanguageId"
                                                v-model="SelectedEmailLayout.LanguageId"
                                                :options="CompanyLanguageOptions"
                                                :required="true"
                                                :select-class="errors.LanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="Language"
                                                @change="resetErrors()"
                                            />
                                            <InputErrorMessages v-if="errors.LanguageId"
                                                                :errorMessages="errors.LanguageId"></InputErrorMessages>
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
                                @click="copyEmailLayoutToCompany">
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
