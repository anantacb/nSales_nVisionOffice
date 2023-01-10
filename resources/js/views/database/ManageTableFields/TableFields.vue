<script setup>
import {onMounted, ref} from "vue";
import {onBeforeRouteLeave, useRoute} from "vue-router";
import {useTemplateStore} from "@/stores/template";
import TableFieldsForm from "@/views/database/ManageTableFields/TableFieldsForm.vue";
import Table from "@/models/Table";
import Modal from "@/components/ui/Modal/Modal.vue";
import TableField from "@/models/TableField";
import {useNotificationStore} from "@/stores/notification";
import router from "@/router";

const route = useRoute();
const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

onMounted(() => {
    templateStore.sidebarMini({mode: 'on'}); // For Making the main container Wide
    getTableDetails();
});

onBeforeRouteLeave((to, from) => {
    templateStore.sidebarMini({mode: 'off'});
});

// Get Table details Section
let table = ref({});
let selectCompaniesOptions = ref([]);

async function getTableDetails() {
    let {data} = await Table.getDetails(route.params.id);
    table.value = data.data;

    let tableCompanies = data.data.company_tables.map((company_table) => {
        return {
            label: company_table.company.Name,
            value: company_table.company.Id
        }
    });

    tableCompanies = _.orderBy(tableCompanies, ['label'], ['asc']);

    if (_.isEmpty(tableCompanies)) {
        selectCompaniesOptions = data.data.module.companies.map((company) => {
            return {
                label: company.Name,
                value: company.Id
            }
        });
        selectCompaniesOptions = _.orderBy(selectCompaniesOptions, ['label'], ['asc'])
    } else {
        selectCompaniesOptions = tableCompanies;
    }
}

// end

const tableFieldsForm = ref(null);
const tableFieldsRef = ref(null);

function addNewField() {
    tableFieldsForm.value.addNewField();
}

let sqlPreviewData = ref([]);
let sqlFormData = ref({});
let modal = ref(null);

function showPreviewModal(queries, formData) {
    sqlPreviewData.value = queries;
    sqlFormData.value = formData;
    modal.value.openModal();
}

function previewSql() {
    tableFieldsForm.value.getSqlPreviews();
}

function startLoading() {
    tableFieldsRef.value.statusLoading();
}

function endLoading() {
    tableFieldsRef.value.statusNormal();
}

async function saveWithoutExecuting() {
    templateStore.pageLoader({mode: 'on'});
    try {
        let {data} = await TableField.tableFieldsOperationsSaveWithoutExecuting(table.value.Id, sqlFormData.value);
        modal.value.closeModal();
        await router.push({name: 'tables'});
        notificationStore.showNotification(data.message);
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error');
    }
    templateStore.pageLoader({mode: 'off'});
}

async function saveAndExecute() {
    templateStore.pageLoader({mode: 'on'});
    try {
        let {data} = await TableField.tableFieldsOperationsSaveAndExecute(table.value.Id, sqlFormData.value);
        modal.value.closeModal();
        //await router.push({name: 'manage-table-fields', params: {id: table.value.Id}});
        await router.push({name: 'tables'});
        notificationStore.showNotification(data.message);
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error');
    }
    templateStore.pageLoader({mode: 'off'});
}

</script>

<template>
    <!-- Hero -->
    <!--    <BasePageHeading>
            <template #extra>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li aria-current="page" class="breadcrumb-item">Table</li>
                        <li class="breadcrumb-item">
                            <router-link :to="{name: 'tables'}" class="link-fx">Tables</router-link>
                        </li>
                    </ol>
                </nav>
            </template>
        </BasePageHeading>-->
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock
            ref="tableFieldsRef"
            :title="table.Name"
            btn-option-fullscreen
        >
            <template #options>
                <button class="btn btn-sm btn-outline-primary me-1" type="button"
                        @click="addNewField">Add Field
                </button>
            </template>

            <TableFieldsForm ref="tableFieldsForm"
                             :select-companies-options="selectCompaniesOptions"
                             :table="table"
                             @endLoading="endLoading"
                             @showPreview="showPreviewModal"
                             @startLoading="startLoading"
            ></TableFieldsForm>

            <template v-slot:footer>
                <button class="btn btn-outline-primary" @click="previewSql">Preview Sql</button>
            </template>
        </BaseBlock>

        <Modal id="queries" ref="modal">
            <template v-slot:modal-content>
                <BaseBlock class="mb-0" transparent>
                    <!--                    <template #options>
                                            <button aria-label="Close" class="btn-block-option" data-bs-dismiss="modal" type="button">
                                                <i class="fa fa-fw fa-times"></i>
                                            </button>
                                        </template>-->

                    <template #content>
                        <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                            <li class="nav-item">
                                <button
                                    id="btabs-static-home-tab"
                                    aria-controls="btabs-static-home"
                                    aria-selected="true"
                                    class="nav-link active"
                                    data-bs-target="#mysql-tab"
                                    data-bs-toggle="tab"
                                    role="tab"
                                    type="button"
                                >
                                    MySql
                                </button>
                            </li>
                            <!--                            <li class="nav-item">
                                                            <button
                                                                id="btabs-static-profile-tab"
                                                                aria-controls="btabs-static-profile"
                                                                aria-selected="false"
                                                                class="nav-link"
                                                                data-bs-target="#sqlite-tab"
                                                                data-bs-toggle="tab"
                                                                role="tab"
                                                                type="button"
                                                            >
                                                                Sqlite
                                                            </button>
                                                        </li>-->
                            <li class="nav-item ms-auto">
                                <button aria-label="Close" class="btn-block-option" data-bs-dismiss="modal"
                                        type="button">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </li>
                        </ul>
                        <div class="block-content tab-content fs-sm scrollable-modal-content">
                            <div
                                id="mysql-tab"
                                aria-labelledby="btabs-static-home-tab"
                                class="tab-pane active"
                                role="tabpanel"
                                tabindex="0"
                            >
                                <template v-if="sqlPreviewData.sqlPreview">
                                    <p v-for="sqlPreview in sqlPreviewData.sqlPreviews"
                                       v-html="sqlPreview">
                                    </p>
                                </template>
                                <template v-else>
                                    <p v-html="sqlPreviewData.sqlPreviewMessage"></p>
                                </template>

                            </div>
                            <!--                            <div
                                                            id="sqlite-tab"
                                                            aria-labelledby="btabs-static-profile-tab"
                                                            class="tab-pane"
                                                            role="tabpanel"
                                                            tabindex="0"
                                                        >
                                                            <template v-if="sqlPreviewData.sqlitePreview">
                                                                <p v-for="sqlitePreview in sqlPreviewData.sqlitePreviews"
                                                                   v-html="sqlitePreview">
                                                                </p>
                                                            </template>
                                                            <template v-else>
                                                                <p v-html="sqlPreviewData.sqlitePreviewMessage"></p>
                                                            </template>
                                                        </div>-->
                        </div>
                        <!--                        <div class="block-content fs-sm scrollable-content">
                                                    <p v-for="sqlPreview in sqlPreviews" class="preview-sql-blocks" v-html="sqlPreview">
                                                    </p>
                                                </div>-->
                        <div class="block-content block-content-full text-end bg-body">
                            <button class="btn btn-sm btn-outline-danger me-1" data-bs-dismiss="modal" type="button">
                                Cancel
                            </button>
                            <button class="btn btn-sm btn-outline-primary me-1" type="button"
                                    @click="saveWithoutExecuting">
                                Save Without Execute
                            </button>
                            <button class="btn btn-sm btn-outline-success" type="button" @click="saveAndExecute">
                                Save and Execute
                            </button>
                        </div>
                    </template>
                </BaseBlock>
            </template>
        </Modal>
    </div>
    <!-- END Page Content -->
</template>
