<script setup>
import {onMounted, ref} from "vue";
import {booleanOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
import {useFormErrors} from "@/composables/useFormErrors";
import TableHelper from "@/models/TableHelper";
import Application from "@/models/Office/Application";
import Module from "@/models/Office/Module";
import Company from "@/models/Office/Company";
import Role from "@/models/Office/Role";
import Table from "@/models/Office/Table";
import DataFilter from "@/models/Office/DataFilter";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Type = ref('Filter');
let Disabled = ref(0);

let ApplyTo = ref('');
let ModuleId = ref(null);
let ApplicationId = ref(null);
let CompanyId = ref(null);
let RoleId = ref(null);
let TableId = ref(null);
let CompanyUserId = ref(null);

let Description = ref("");
let Value = ref("");
let ValueExpression = ref("");


let ApplicationOptions = ref([]);
let CompanyOptions = ref([]);
let RoleOptions = ref([]);
let CompanyUserOptions = ref([]);
let ModuleOptions = ref([]);
let TableOptions = ref([]);

let visibleDropdowns = ref({
    Application: false,
    Company: false,
    Role: false,
    User: false,
    Module: false,
    Table: false
});

const ApplyToOptions = ['', 'Application', 'Company', 'Role', 'User'].map((item) => {
    return {
        label: item ? item : 'Please Select',
        value: item
    }
});

const createDataFilterRef = ref(null);

let TypeOptions = ref([]);

async function getTypes() {
    let {data: TypeData} = await TableHelper.getEnumValues('Office', `DataFilter`, 'Type');
    TypeOptions.value = TypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

function resetApplyToData() {
    ApplicationId.value = null;
    CompanyId.value = null;
    RoleId.value = null;
    CompanyUserId.value = null;
    ModuleId.value = null;
    TableId.value = null;

    ApplicationOptions.value = [];
    CompanyOptions.value = [];
    RoleOptions.value = [];
    CompanyUserOptions.value = [];
    ModuleOptions.value = [];
    TableOptions.value = [];
}

function resetApplyToDropDownVisibility() {
    visibleDropdowns.value = {
        Application: false,
        Company: false,
        Role: false,
        User: false,
        Module: false,
        Table: false
    };
}

function makeApplyToDropdownsVisible(selectedDropdowns) {
    selectedDropdowns.map((dropdown) => {
        visibleDropdowns.value[dropdown] = true;
    });
}

async function applyToChanged() {
    createDataFilterRef.value.statusLoading();
    resetApplyToData();
    resetApplyToDropDownVisibility();

    switch (ApplyTo.value) {
        case 'Application':
            await getApplications();
            makeApplyToDropdownsVisible(['Application', 'Module', 'Table']);
            break;
        case 'Company':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'Module', 'Table']);
            break;
        case 'Role':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'Role', 'Module', 'Table']);
            break;
        case 'User':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'User', 'Module', 'Table']);
            break;
        default:
            break;
    }
    createDataFilterRef.value.statusNormal();
}

async function getApplications() {
    const {data} = await Application.getAllApplications();
    let options = [{label: 'Select Application', value: ''}];

    data.forEach((application) => {
        let option = {label: application.Name, value: application.Id};
        options.push(option);
    });

    ApplicationOptions.value = options;
}

async function applicationChanged() {
    ModuleOptions.value = [];
    if (!ApplicationId.value) {
        return;
    }
    createDataFilterRef.value.statusLoading();
    await getModulesByApplication(ApplicationId.value);
    createDataFilterRef.value.statusNormal();
}

async function getModulesByApplication() {
    let {data} = await Module.getModulesByApplication(ApplicationId.value);
    let options = [{label: 'Select Module', value: ''}];

    data.forEach((module) => {
        let option = {label: module.Name, value: module.Id};
        options.push(option);
    });

    ModuleOptions.value = options;
}

async function getCompanies() {
    const {data} = await Company.getAllCompanies();
    let options = [{label: 'Select Company', value: ''}];

    data.forEach((company) => {
        let option = {label: company.Name, value: company.Id};
        options.push(option);
    });

    CompanyOptions.value = options;
}

async function companyChanged() {
    ModuleOptions.value = [];
    RoleOptions.value = [];
    CompanyUserOptions.value = [];

    if (!CompanyId.value) {
        return;
    }

    createDataFilterRef.value.statusLoading();

    if (ApplyTo.value === 'Company') {
        await getModulesByCompany();
    } else if (ApplyTo.value === 'Role') {
        await getRolesByCompany();
    } else if (ApplyTo.value === 'User') {
        await getAllCompanyUsers();
    }

    createDataFilterRef.value.statusNormal();
}

async function getModulesByCompany() {
    const {data} = await Module.getActivatedModulesByCompany(CompanyId.value);
    let options = [{label: 'Select Module', value: ''}];

    data.forEach((module) => {
        let option = {label: module.Name, value: module.Id};
        options.push(option);
    });

    ModuleOptions.value = options;
}

async function getTablesByModule() {
    const {data} = await Table.getTablesByModule(ModuleId.value);
    let options = [{label: 'Select Table', value: ''}];

    data.forEach((table) => {
        let option = {label: table.Name, value: table.Id};
        options.push(option);
    });

    TableOptions.value = options;
}

async function getRolesByCompany() {
    const {data} = await Role.getRolesByCompany(CompanyId.value, true);

    let options = [{label: 'Select Role', value: ''}];
    data.forEach((role) => {
        let option = {label: role.Name, value: role.Id};
        options.push(option);
    });
    RoleOptions.value = options;
}

async function roleChanged() {
    ModuleOptions.value = [];

    if (!RoleId.value) {
        return;
    }

    createDataFilterRef.value.statusLoading();
    await getModulesByCompany();
    createDataFilterRef.value.statusNormal();
}

async function getAllCompanyUsers() {
    const {data} = await User.getAllCompanyUsers(CompanyId.value);

    let options = [{label: 'Select User', value: ''}];

    data.forEach((company_user) => {
        let option = {label: company_user.user.Name, value: company_user.Id};
        options.push(option);
    });

    CompanyUserOptions.value = options;
}

async function companyUserChanged() {
    ModuleOptions.value = [];

    if (!CompanyUserId.value) {
        return;
    }

    createDataFilterRef.value.statusLoading();
    await getModulesByCompany();
    createDataFilterRef.value.statusNormal();
}

async function moduleChanged() {
    TableOptions.value = [];

    if (!ModuleId.value) {
        return;
    }

    createDataFilterRef.value.statusLoading();
    await getTablesByModule();
    createDataFilterRef.value.statusNormal();
}

async function createDataFilter() {
    createDataFilterRef.value.statusLoading();

    let formData = {
        Name: Name.value,
        Type: Type.value,
        Description: Description.value,
        Disabled: Disabled.value,
        Value: Value.value,
        ValueExpression: ValueExpression.value,
        ApplyTo: ApplyTo.value,
        ModuleId: ModuleId.value,
        ApplicationId: ApplicationId.value,
        CompanyId: CompanyId.value,
        RoleId: RoleId.value,
        CompanyUserId: CompanyUserId.value,
        TableId: TableId.value,
    };

    try {
        let {data, message} = await DataFilter.create(formData);
        createDataFilterRef.value.statusNormal();
        await router.push({name: 'data-filters'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createDataFilterRef.value.statusNormal();
    }
}

onMounted(async () => {
    createDataFilterRef.value.statusLoading();
    await getTypes();
    createDataFilterRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="createDataFilterRef" content-full title="Create Data Filter">

            <template #options>
                <router-link :to="{name:'data-filters'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createDataFilter">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Filter</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Name"
                                       required
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="Type" :options="TypeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Type"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Disabled">
                                Disabled<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Disabled" v-model="Disabled" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Disabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Disabled"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Disabled"
                                                    :errorMessages="errors.Disabled"></InputErrorMessages>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Setting</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ApplyTo">
                                ApplyTo<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="ApplyTo" v-model="ApplyTo" :options="ApplyToOptions"
                                        :required="true"
                                        :select-class="errors.ApplyTo ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ApplyTo"
                                        @change="applyToChanged"/>
                                <InputErrorMessages v-if="errors.ApplyTo"
                                                    :errorMessages="errors.ApplyTo"></InputErrorMessages>
                            </div>
                        </div>
                        <div v-if="visibleDropdowns.Application" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Application">
                                Application<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Application" v-model="ApplicationId" :options="ApplicationOptions"
                                        :required="true"
                                        :select-class="errors.Application ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Application"
                                        @change="applicationChanged"/>
                                <InputErrorMessages v-if="errors.Application"
                                                    :errorMessages="errors.Application"></InputErrorMessages>
                            </div>
                        </div>

                        <div v-if="visibleDropdowns.Company" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Company">
                                Company<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Company" v-model="CompanyId" :options="CompanyOptions"
                                        :required="true"
                                        :select-class="errors.Company ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Company"
                                        @change="companyChanged"/>
                                <InputErrorMessages v-if="errors.CompanyId"
                                                    :errorMessages="errors.CompanyId"></InputErrorMessages>
                            </div>
                        </div>

                        <div v-if="visibleDropdowns.Role" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Role">
                                Role<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Role" v-model="RoleId" :options="RoleOptions"
                                        :required="true"
                                        :select-class="errors.Role ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Role"
                                        @change="roleChanged"/>
                                <InputErrorMessages v-if="errors.Role"
                                                    :errorMessages="errors.Role"></InputErrorMessages>
                            </div>
                        </div>

                        <div v-if="visibleDropdowns.User" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="User">
                                User<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="User" v-model="CompanyUserId" :options="CompanyUserOptions"
                                        :required="true"
                                        :select-class="errors.User ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="User"
                                        @change="companyUserChanged"/>
                                <InputErrorMessages v-if="errors.CompanyUserId"
                                                    :errorMessages="errors.CompanyUserId"></InputErrorMessages>
                            </div>
                        </div>

                        <div v-if="visibleDropdowns.Module" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Module">
                                Module<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Module" v-model="ModuleId" :options="ModuleOptions"
                                        :required="true"
                                        :select-class="errors.Module ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Module"
                                        @change="moduleChanged"/>
                                <InputErrorMessages v-if="errors.Module"
                                                    :errorMessages="errors.Module"></InputErrorMessages>
                            </div>
                        </div>

                        <div v-if="visibleDropdowns.Table" class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Table">
                                Table<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Table" v-model="TableId" :options="TableOptions"
                                        :required="true"
                                        :select-class="errors.Table ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Table"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Table"
                                                    :errorMessages="errors.Table"></InputErrorMessages>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="row">
                    <div class="col-lg-8 space-y-2">
                        <h5 class="fw-light text-center">Values</h5>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-10">
                            <textarea id="Description" v-model="Description" :class="{'is-invalid': errors.Description}"
                                      class="form-control" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.Description"
                                                    :errorMessages="errors.Description"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Value">
                                Value
                            </label>
                            <div class="col-sm-10">
                            <textarea id="Value" v-model="Value" :class="{'is-invalid': errors.Value}"
                                      class="form-control" rows="4">
                            </textarea>
                                <InputErrorMessages v-if="errors.Value"
                                                    :errorMessages="errors.Value"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="ValueExpression">
                                ValueExpression
                            </label>
                            <div class="col-sm-10">
                            <textarea id="ValueExpression" v-model="ValueExpression"
                                      :class="{'is-invalid': errors.ValueExpression}"
                                      class="form-control" rows="4">
                            </textarea>
                                <InputErrorMessages v-if="errors.ValueExpression"
                                                    :errorMessages="errors.ValueExpression"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>

        </BaseBlock>

    </div>
</template>
