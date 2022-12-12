<script setup>
import CreateTableForm from "@/views/database/Tables/CreateTableForm.vue";
import Modal from "@/components/ui/Modal/Modal.vue";
import {ref} from "vue";
import Table from "@/models/Table";

let sqlPreviewData = ref([]);
let sqlFormData = ref({});
let modal = ref(null);

function showPreviewModal(queries, formData) {
    sqlPreviewData.value = queries;
    sqlFormData.value = formData;
    modal.value.openModal();
}

async function saveAndExecute() {
    try {
        let {data} = await Table.createTableSaveAndExecute(sqlFormData.value);
    } catch (error) {
        console.log(error);
    }

}

function saveWithoutExecuting() {

}

</script>

<template>
    <!-- Hero -->
    <BasePageHeading subtitle="" title="Create Table">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li aria-current="page" class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item">
                        <router-link :to="{name: 'create-table'}" class="link-fx">Create Table</router-link>
                    </li>
                </ol>
            </nav>
        </template>
    </BasePageHeading>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock content-full title="Create Table">
            <CreateTableForm @showPreview="showPreviewModal"/>
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
                        <div class="block-content tab-content fs-sm scrollable-content">
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

<style lang="scss" scoped>
.scrollable-content {
    max-height: 80vh;
    overflow: scroll;
}
</style>
