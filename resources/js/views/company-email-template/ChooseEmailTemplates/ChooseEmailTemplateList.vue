<script setup>
import {onMounted, ref} from 'vue';
import Swal from 'sweetalert2';
import {useNotificationStore} from "@/stores/notificationStore";
import useGridManagement from "@/composables/useGridManagement";
import EmailTemplate from "@/models/Office/EmailTemplate";
import {useCompanyStore} from "@/stores/companyStore";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import CompanyEmailTemplate from "@/models/Company/CompanyEmailTemplate";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";
import EmailLayout from "@/models/Office/EmailLayout";
import Loader from "@/components/ui/Loader/Loader.vue";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

const isLoading = ref(false);
let tableData = ref([]);
let paginationData = ref(null);
let previewTemplateRef = ref(null);
let copyTemplateRef = ref(null);

let EmailEventOptions = ref([]);
let EmailEvents = ref([]);
const previewTemplate = ref('');
const previewSubject = ref('');

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

async function showPreviewModal(emailTemplate, index) {
    isLoading.value = true;
    await getDataForPreview(emailTemplate);
    previewTemplateRef.value.openModal();
    isLoading.value = false;
}

function showCopyModal() {
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

onMounted(async () => {
    await getEmailTemplatesForCompany();
    await getEmailEvents();
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
                    @click="showCopyModal(props.data, props.index)">
                <i class="fa fa-copy"></i>
            </button>
            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                    @click="showPreviewModal(props.data, props.index)">
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
                        <div
                            id="mysql-tab"
                            aria-labelledby="btabs-static-home-tab"
                            class="tab-pane active"
                            role="tabpanel"
                            tabindex="0"
                        >
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
                    <div class="block-content tab-content fs-sm scrollable-modal-content">
                        <div
                            id="mysql-tab"
                            aria-labelledby="btabs-static-home-tab"
                            class="tab-pane active"
                            role="tabpanel"
                            tabindex="0"
                        >
                            <!-- Content goes here -->

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
