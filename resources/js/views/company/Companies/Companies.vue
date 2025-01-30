<script setup>
import CompanyList from "@/views/company/Companies/CompanyList.vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import slugify from "@sindresorhus/slugify";
import {ref, watch} from "vue";
import {useFormErrors} from "@/composables/useFormErrors";
import Company from "@/models/Office/Company";
import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();

const modal = ref(null);

let refreshData = ref(0);

let {errors, setErrors, resetErrors} = useFormErrors();

let SourceCompanyId = ref(null);
let SourceAccountName = ref('');
let SourceDomainName = ref('');

let Name = ref('');
let CompanyName = ref('');
let DomainName = ref('');
let DatabaseName = ref('');

let WithSettings = ref(true);
let WithDataFilters = ref(true);
let WithEmailLayoutsAndTemplates = ref(true);
let WithRolesAndUsers = ref(true);
let WithEmailConfigurations = ref(true);
let WithLanguageAndTranslations = ref(true);
let WithData = ref(true);

function showCloneCompanyModal(SrcCompanyId, SrcAccountName, SrcDomainName) {
    SourceCompanyId.value = SrcCompanyId;
    SourceAccountName.value = SrcAccountName;
    SourceDomainName.value = SrcDomainName;

    Name.value = '';
    CompanyName.value = '';
    DomainName.value = '';
    DatabaseName.value = '';

    WithRolesAndUsers.value = true;
    WithSettings.value = true;
    WithData.value = true;
    WithDataFilters.value = true;
    WithEmailLayoutsAndTemplates.value = true;
    WithEmailConfigurations.value = true;
    WithLanguageAndTranslations.value = true;

    // Open Modal
    modal.value.openModal();
}

async function cloneCompany() {
    let formData = {
        SourceCompanyId: SourceCompanyId.value,
        Name: Name.value,
        CompanyName: CompanyName.value,
        DomainName: DomainName.value,
        DatabaseName: DatabaseName.value,

        WithRolesAndUsers: WithRolesAndUsers.value,
        WithSettings: WithSettings.value,
        WithData: WithData.value,
        WithDataFilters: WithDataFilters.value,
        WithEmailLayoutsAndTemplates: WithEmailLayoutsAndTemplates.value,
        WithEmailConfigurations: WithEmailConfigurations.value,
        WithLanguageAndTranslations: WithLanguageAndTranslations.value,
    }

    let {data, message} = await Company.cloneCompany(formData);
    modal.value.closeModal();
    refreshData.value++;
    notificationStore.showNotification(message);
}

watch(Name, (newName, oldName) => {
    DomainName.value = slugify(newName, {
        separator: '',
        customReplacements: [
            ['&', '']
        ]
    });
    DatabaseName.value = `nvisiondb_${DomainName.value}`;
});

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Companies">
            <CompanyList :refresh-data="refreshData" @showCloneCompanyModal="showCloneCompanyModal"/>
        </BaseBlock>

        <ModalComponent id="cloneCompanyModal" ref="modal" modal-body-classes="modal-xl">
            <template v-slot:modal-content>
                <BaseBlock ref="assignToCompanyRef" :title="`Cloning from ${SourceAccountName} (${SourceDomainName})`"
                           class="mb-0" transparent>
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
                        <form @submit.prevent="cloneCompany">
                            <div class="block-content fs-sm space-y-2 mb-2">
                                <div class="row">
                                    <div class="col-lg-6 space-y-2">
                                        <div class="row">
                                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                                Account Name<span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input id="Name" v-model="Name"
                                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                                       autocomplete="off" class="form-control" name="Name"
                                                       placeholder="Name"
                                                       required
                                                       type="text"/>
                                                <InputErrorMessages v-if="errors.Name"
                                                                    :errorMessages="errors.Name"></InputErrorMessages>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-4 col-form-label col-form-label-sm" for="DomainName">
                                                Domain Name<span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input id="DomainName" v-model="DomainName"
                                                       :class="errors.DomainName ? `is-invalid form-select-sm` : `form-select-sm`"
                                                       class="form-control" disabled
                                                       name="DomainName" required
                                                       type="text"/>
                                                <InputErrorMessages v-if="errors.DomainName"
                                                                    :errorMessages="errors.DomainName"></InputErrorMessages>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 space-y-2">
                                        <div class="row">
                                            <label class="col-sm-4 col-form-label col-form-label-sm" for="CompanyName">
                                                Company Name<span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input id="CompanyName" v-model="CompanyName"
                                                       :class="errors.CompanyName ? `is-invalid form-control-sm` : `form-control-sm`"
                                                       class="form-control" name="CompanyName"
                                                       required
                                                       type="text"/>
                                                <InputErrorMessages v-if="errors.CompanyName"
                                                                    :errorMessages="errors.CompanyName"></InputErrorMessages>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-4 col-form-label col-form-label-sm" for="DatabaseName">
                                                Database Name<span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input id="DatabaseName" v-model="DatabaseName"
                                                       :class="errors.DatabaseName ? `is-invalid form-control-sm` : `form-control-sm`"
                                                       class="form-control" disabled name="DatabaseName"
                                                       placeholder="" required
                                                       type="text"/>
                                                <InputErrorMessages v-if="errors.DatabaseName"
                                                                    :errorMessages="errors.DatabaseName"></InputErrorMessages>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 space-y-2 mt-2">
                                        <label class="col-form-label col-form-label-sm">
                                            Copy Contents Of
                                        </label>
                                        <Switch id="WithRolesAndUsers" v-model="WithRolesAndUsers"
                                                label="Roles And Users" name="WithRolesAndUsers"/>
                                        <Switch id="WithSettings" v-model="WithSettings" label="Settings"
                                                name="WithSettings"/>
                                        <Switch id="WithData" v-model="WithData" label="Data"
                                                name="WithData"/>
                                        <Switch id="WithDataFilters" v-model="WithDataFilters" label="Data Filters"
                                                name="WithDataFilters"/>
                                        <Switch id="WithEmailLayoutsAndTemplates" v-model="WithEmailLayoutsAndTemplates"
                                                label="Email Layouts and Templates"
                                                name="WithEmailLayoutsAndTemplates"/>
                                        <Switch id="WithEmailConfigurations" v-model="WithEmailConfigurations"
                                                label="Email Configurations" name="WithEmailConfigurations"/>
                                        <Switch id="WithLanguageAndTranslations" v-model="WithLanguageAndTranslations"
                                                label="Language And Translations" name="WithLanguageAndTranslations"/>
                                    </div>
                                </div>
                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                                <button class="btn btn-sm btn-primary me-1" type="submit">
                                    Clone
                                </button>
                                <button class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal" type="button">
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
