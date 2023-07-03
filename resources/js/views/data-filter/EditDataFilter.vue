<script setup>
import {onMounted, ref} from "vue";
import {booleanOptions} from "@/data/dropDownOptions";
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
import {onBeforeRouteUpdate, useRoute} from "vue-router";

const route = useRoute();

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

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

const updateDataFilterRef = ref(null);

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
    DataFilterModel.value.ApplicationId = null;
    DataFilterModel.value.CompanyId = null;
    DataFilterModel.value.RoleId = null;
    DataFilterModel.value.CompanyUserId = null;
    DataFilterModel.value.ModuleId = null;
    DataFilterModel.value.TableId = null;

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
    updateDataFilterRef.value.statusLoading();
    resetApplyToData();
    resetApplyToDropDownVisibility();

    switch (DataFilterModel.value.ApplyTo) {
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
    updateDataFilterRef.value.statusNormal();
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
    if (!DataFilterModel.value.ApplicationId) {
        return;
    }
    updateDataFilterRef.value.statusLoading();
    await getModulesByApplication(DataFilterModel.value.ApplicationId);
    updateDataFilterRef.value.statusNormal();
}

async function getModulesByApplication() {
    let {data} = await Module.getModulesByApplication(DataFilterModel.value.ApplicationId);
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

    if (!DataFilterModel.value.CompanyId) {
        return;
    }

    updateDataFilterRef.value.statusLoading();

    if (DataFilterModel.value.ApplyTo === 'Company') {
        await getModulesByCompany();
    } else if (DataFilterModel.value.ApplyTo === 'Role') {
        await getRolesByCompany();
    } else if (DataFilterModel.value.ApplyTo === 'User') {
        await getAllCompanyUsers();
    }

    updateDataFilterRef.value.statusNormal();
}

async function getModulesByCompany() {
    const {data} = await Module.getActivatedModulesByCompany(DataFilterModel.value.CompanyId);
    let options = [{label: 'Select Module', value: ''}];

    data.forEach((module) => {
        let option = {label: module.Name, value: module.Id};
        options.push(option);
    });

    ModuleOptions.value = options;
}

async function getTablesByModule() {
    const {data} = await Table.getTablesByModule(DataFilterModel.value.ModuleId);
    let options = [{label: 'Select Table', value: ''}];

    data.forEach((table) => {
        let option = {label: table.Name, value: table.Id};
        options.push(option);
    });

    TableOptions.value = options;
}

async function getRolesByCompany() {
    const {data} = await Role.getRolesByCompany(DataFilterModel.value.CompanyId, true);

    let options = [{label: 'Select Role', value: ''}];
    data.forEach((role) => {
        let option = {label: role.Name, value: role.Id};
        options.push(option);
    });
    RoleOptions.value = options;
}

async function roleChanged() {
    ModuleOptions.value = [];

    if (!DataFilterModel.value.RoleId) {
        return;
    }

    updateDataFilterRef.value.statusLoading();
    await getModulesByCompany();
    updateDataFilterRef.value.statusNormal();
}

async function getAllCompanyUsers() {
    const {data} = await User.getAllCompanyUsers(DataFilterModel.value.CompanyId);

    let options = [{label: 'Select User', value: ''}];

    data.forEach((company_user) => {
        let option = {label: company_user.user.Name, value: company_user.Id};
        options.push(option);
    });

    CompanyUserOptions.value = options;
}

async function companyUserChanged() {
    ModuleOptions.value = [];

    if (!DataFilterModel.value.CompanyUserId) {
        return;
    }

    updateDataFilterRef.value.statusLoading();
    await getModulesByCompany();
    updateDataFilterRef.value.statusNormal();
}

async function moduleChanged() {
    TableOptions.value = [];

    if (!DataFilterModel.value.ModuleId) {
        return;
    }

    updateDataFilterRef.value.statusLoading();
    await getTablesByModule();
    updateDataFilterRef.value.statusNormal();
}

async function updateDataFilter() {
    updateDataFilterRef.value.statusLoading();

    let formData = {
        Id: DataFilterModel.value.Id,
        Name: DataFilterModel.value.Name,
        Type: DataFilterModel.value.Type,
        Description: DataFilterModel.value.Description,
        Disabled: DataFilterModel.value.Disabled,
        Value: DataFilterModel.value.Value,
        ValueExpression: DataFilterModel.value.ValueExpression,
        ApplyTo: DataFilterModel.value.ApplyTo,
        ModuleId: DataFilterModel.value.ModuleId,
        ApplicationId: DataFilterModel.value.ApplicationId,
        CompanyId: DataFilterModel.value.CompanyId,
        RoleId: DataFilterModel.value.RoleId,
        CompanyUserId: DataFilterModel.value.CompanyUserId,
        TableId: DataFilterModel.value.TableId,
    };

    try {
        let {data, message} = await DataFilter.update(formData);
        updateDataFilterRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        updateDataFilterRef.value.statusNormal();
    }
}

onMounted(async () => {
    updateDataFilterRef.value.statusLoading();
    await getTypes();
    await getDataFilterDetails();
    switch (DataFilterModel.value.ApplyTo) {
        case 'Application':
            await getApplications();
            await getModulesByApplication();
            await getTablesByModule();
            makeApplyToDropdownsVisible(['Application', 'Module', 'Table']);
            break;
        case 'Company':
            await getCompanies();
            await getModulesByCompany();
            await getTablesByModule();
            makeApplyToDropdownsVisible(['Company', 'Module', 'Table']);
            break;
        case 'Role':
            await getCompanies();
            DataFilterModel.value.CompanyId = DataFilterModel.value.role.CompanyId;
            await getRolesByCompany();
            await getModulesByCompany();
            await getTablesByModule();
            makeApplyToDropdownsVisible(['Company', 'Role', 'Module', 'Table']);
            break;
        case 'User':
            await getCompanies();
            DataFilterModel.value.CompanyId = DataFilterModel.value.company_user.CompanyId;
            await getAllCompanyUsers();
            await getModulesByCompany();
            await getTablesByModule();
            makeApplyToDropdownsVisible(['Company', 'User', 'Module', 'Table']);
            break;
        default:
            break;
    }
    updateDataFilterRef.value.statusNormal();
});

let DataFilterModel = ref({});

async function getDataFilterDetails() {
    let {data} = await DataFilter.details(route.params.id);
    DataFilterModel.value = data;
}

onBeforeRouteUpdate((to, from) => {
    console.log("onBeforeRouteUpdate");
    console.log(to, from);
});


let backButtonRoute = localStorage.getItem('data-filter-back-route') ?? 'data-filters';

</script>

<template>
    <div class="content">
        <BaseBlock ref="updateDataFilterRef" content-full title="Edit Data Filter">

            <template #options>
                <router-link :to="{name: backButtonRoute}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateDataFilter">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Filter</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="DataFilterModel.Name"
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
                                <Select id="Type" v-model="DataFilterModel.Type" :options="TypeOptions"
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
                                <Select id="Disabled" v-model="DataFilterModel.Disabled" :options="booleanOptions"
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
                                <Select id="ApplyTo" v-model="DataFilterModel.ApplyTo" :options="ApplyToOptions"
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
                                <Select id="Application" v-model="DataFilterModel.ApplicationId"
                                        :options="ApplicationOptions"
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
                                <Select id="Company" v-model="DataFilterModel.CompanyId" :options="CompanyOptions"
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
                                <Select id="Role" v-model="DataFilterModel.RoleId" :options="RoleOptions"
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
                                <Select id="User" v-model="DataFilterModel.CompanyUserId"
                                        :options="CompanyUserOptions"
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
                                <Select id="Module" v-model="DataFilterModel.ModuleId" :options="ModuleOptions"
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
                                <Select id="Table" v-model="DataFilterModel.TableId" :options="TableOptions"
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
                            <textarea id="Description" v-model="DataFilterModel.Description"
                                      :class="{'is-invalid': errors.Description}"
                                      class="form-control" rows="2" @keyup="resetErrors">
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
                            <textarea id="Value" v-model="DataFilterModel.Value" :class="{'is-invalid': errors.Value}"
                                      class="form-control" rows="4" @keyup="resetErrors">
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
                            <textarea id="ValueExpression" v-model="DataFilterModel.ValueExpression"
                                      :class="{'is-invalid': errors.ValueExpression}"
                                      class="form-control" rows="4" @keyup="resetErrors">
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
