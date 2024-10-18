<script setup>
import DataFilterList from "@/views/data-filter/CompanyDataFilters/CompanyDataFilterList.vue";
import {useCompanyStore} from "@/stores/companyStore";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import User from "@/models/Office/User";
import {onMounted, ref, watch} from "vue";
import DataFilter from "@/models/Office/DataFilter";
import _ from "lodash";

const companyStore = useCompanyStore();

const CompanyUserOptions = ref([]);

async function getAllCompanyUsers() {
    const {data} = await User.getAllCompanyUsers(companyStore.selectedCompany.Id);

    let options = [{label: 'Select User', value: ''}];

    data.forEach((company_user) => {
        let option = {label: company_user.user.Name, value: company_user.Id};
        options.push(option);
    });

    CompanyUserOptions.value = options;
}

let DataFilterId = ref(null);
let CompanyUserId = ref('');
const modal = ref(null);

function showFilterResultModal(id) {
    DataFilterId.value = id;

    // Clear Modal Data
    Query.value = '';
    RowCount.value = 0;
    ShowResult.value = false;
    CompanyUserId.value = '';

    // Open Modal
    modal.value.openModal();
}

let Query = ref('');
let RowCount = ref(0);

async function getFilterResult() {
    if (!DataFilterId.value || !CompanyUserId.value) {
        return;
    }

    let formData = {
        DataFilterId: DataFilterId.value,
        CompanyUserId: CompanyUserId.value
    };

    let {data, message} = await DataFilter.getFilterResult(formData);

    Query.value = data.Query;
    RowCount.value = data.RowCount;
    ShowResult.value = true;
}

let ShowResult = ref(false);

onMounted(async () => {
    await getAllCompanyUsers();
});

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        await getAllCompanyUsers();
    }
});

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Data Filters (${companyStore.selectedCompany.Name})`">
            <DataFilterList
                @showFilterResultModal="showFilterResultModal"
            />
        </BaseBlock>

        <ModalComponent id="assigneeCompanyFormModal" ref="modal" modal-body-classes="modal-xl">
            <template v-slot:modal-content>
                <BaseBlock ref="assignToCompanyRef" class="mb-0" title="SQL Query Preview" transparent>
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
                        <form @submit.prevent="getFilterResult">
                            <div class="block-content fs-sm space-y-2 mb-2">

                                <div class="row">
                                    <label class="col-sm-3 col-form-label col-form-label-sm"
                                           for="SelectedCompanyUserId">
                                        Select User to generate for:
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-5">
                                        <Select id="SelectedCompanyUserId" v-model="CompanyUserId"
                                                :options="CompanyUserOptions"
                                                :required="true"
                                                name="SelectedCompanyUserId"
                                                select-class="form-select-sm"/>

                                    </div>
                                </div>

                                <div v-if="ShowResult">
                                    <h2>Number of rows: {{ RowCount }}</h2>
                                    <h4>SQL Query</h4>
                                    <p v-html="Query"></p>
                                </div>

                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                                <button
                                    class="btn btn-sm btn-primary me-1"
                                    type="submit"
                                >
                                    Get Preview
                                </button>
                                <button
                                    class="btn btn-sm btn-alt-secondary me-1"
                                    data-bs-dismiss="modal"
                                    type="button">
                                    Close
                                </button>
                            </div>
                        </form>

                    </template>
                </BaseBlock>
            </template>
        </ModalComponent>
    </div>
    <!-- END Page Content -->
</template>
