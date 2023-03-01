<script setup>
import {onMounted, ref} from "vue";
import InputErrorMessages from '@/components/ui/FormElements/InputErrorMessages.vue';
import Select from '@/components/ui/FormElements/Select.vue';
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
import EmailConfiguration from "@/models/Office/EmailConfiguration";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let TemplateType = ref('Internal');
let Disabled = ref('0');

let From = ref('');
let To = ref('');
let Cc = ref('');
let Bcc = ref('');

let SendToCompany = ref('0');
let SendToUser = ref('0');
let SendToCustomer = ref('0');
let SendToSupplier = ref('0');


let Subject = ref("$\"Order Copy #\"+ Order.OrderNumber");
let Body = ref("$\"Dear Customer,\nWe are pleased to send you a copy of the order you have just placed with us.\nPlease find the order copy attached to this email as a PDF document.\nBest Regards\n\"+ Company.Name");
let Description = ref("");
let TemplatePath = ref("Templates\\Order.xslt");

let ApplyTo = ref('');
let ModuleId = ref(null);
let ApplicationId = ref('');
let CompanyId = ref(null);
let RoleId = ref(null);
let CompanyUserId = ref(null);

let ApplicationOptions = ref([]);
let CompanyOptions = ref([]);
let RoleOptions = ref([]);
let CompanyUserOptions = ref([]);
let ModuleOptions = ref([]);

let visibleDropdowns = ref({
    Application: false,
    Company: false,
    Role: false,
    User: false,
    Module: false
});

const ApplyToOptions = ['', 'Application', 'Company', 'Role', 'User'].map((item) => {
    return {
        label: item ? item : 'Please Select',
        value: item
    }
});

const createEmailConfigurationRef = ref(null);

let TemplateTypeOptions = ref([]);

async function getTemplateTypes() {
    let TemplateTypeData = await TableHelper.getEnumValues('Office', `EmailConfiguration`, 'TemplateType');
    TemplateTypeOptions.value = TemplateTypeData.data.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

function resetApplyToData() {
    ModuleId.value = null;
    ApplicationId.value = null;
    CompanyId.value = null;
    RoleId.value = null;
    CompanyUserId.value = null;

    ApplicationOptions.value = [];
    CompanyOptions.value = [];
    RoleOptions.value = [];
    CompanyUserOptions.value = [];
    ModuleOptions.value = [];
}

function resetApplyToDropDownVisibility() {
    visibleDropdowns.value = {
        Application: false,
        Company: false,
        Role: false,
        User: false,
        Module: false
    };
}

function makeApplyToDropdownsVisible(selectedDropdowns) {
    selectedDropdowns.map((dropdown) => {
        visibleDropdowns.value[dropdown] = true;
    });
}

async function applyToChanged() {
    createEmailConfigurationRef.value.statusLoading();
    resetApplyToData();
    resetApplyToDropDownVisibility();

    switch (ApplyTo.value) {
        case 'Application':
            await getApplications();
            makeApplyToDropdownsVisible(['Application', 'Module']);
            break;
        case 'Company':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'Module']);
            break;
        case 'Role':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'Role', 'Module']);
            break;
        case 'User':
            await getCompanies();
            makeApplyToDropdownsVisible(['Company', 'User', 'Module']);
            break;
        default:
            break;
    }
    createEmailConfigurationRef.value.statusNormal();
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
    createEmailConfigurationRef.value.statusLoading();
    await getModulesByApplication(ApplicationId.value);
    createEmailConfigurationRef.value.statusNormal();
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

    createEmailConfigurationRef.value.statusLoading();

    if (ApplyTo.value === 'Company') {
        await getModulesByCompany();
    } else if (ApplyTo.value === 'Role') {
        await getRolesByCompany();
    } else if (ApplyTo.value === 'User') {
        await getCompanyUsers();
    }

    createEmailConfigurationRef.value.statusNormal();
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

async function getRolesByCompany() {
    const {data} = await Role.getRolesByCompany(CompanyId.value);

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

    createEmailConfigurationRef.value.statusLoading();
    await getModulesByCompany();
    createEmailConfigurationRef.value.statusNormal();
}

async function getCompanyUsers() {
    const {data} = await User.getCompanyUsers(CompanyId.value);

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

    createEmailConfigurationRef.value.statusLoading();
    await getModulesByCompany();
    createEmailConfigurationRef.value.statusNormal();
}

async function createEmailConfiguration() {
    createEmailConfigurationRef.value.statusLoading();

    let formData = {
        Name: Name.value,
        TemplateType: TemplateType.value,
        Disabled: Disabled.value,
        From: From.value,
        To: To.value,
        Cc: Cc.value,
        Bcc: Bcc.value,
        SendToCompany: SendToCompany.value,
        SendToUser: SendToUser.value,
        SendToCustomer: SendToCustomer.value,
        SendToSupplier: SendToSupplier.value,
        Subject: Subject.value,
        Body: Body.value,
        Description: Description.value,
        TemplatePath: TemplatePath.value,
        ApplyTo: ApplyTo.value,
        ModuleId: ModuleId.value,
        ApplicationId: ApplicationId.value,
        CompanyId: CompanyId.value,
        RoleId: RoleId.value,
        CompanyUserId: CompanyUserId.value,
    };

    try {
        let {data} = await EmailConfiguration.create(formData);
        createEmailConfigurationRef.value.statusNormal();
        await router.push({name: 'email-configurations'});
        notificationStore.showNotification(data.message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createEmailConfigurationRef.value.statusNormal();
    }

}

onMounted(async () => {
    createEmailConfigurationRef.value.statusLoading();
    await getTemplateTypes();
    createEmailConfigurationRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="createEmailConfigurationRef" content-full title="Create User">

            <form class="space-y-4" @submit.prevent="createEmailConfiguration">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">General</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Name"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
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
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="TemplateType">
                                TemplateType<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="TemplateType" v-model="TemplateType" :options="TemplateTypeOptions"
                                        :required="true"
                                        :select-class="errors.TemplateType ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="TemplateType"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.TemplateType"
                                                    :errorMessages="errors.TemplateType"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Setting</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="From">
                                From
                            </label>
                            <div class="col-sm-8">
                                <input id="From" v-model="From"
                                       :class="errors.From ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="From"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.From"
                                                    :errorMessages="errors.From"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="To">
                                To
                            </label>
                            <div class="col-sm-8">
                                <input id="To" v-model="To"
                                       :class="errors.To ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="To"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.To"
                                                    :errorMessages="errors.To"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Cc">
                                Cc
                            </label>
                            <div class="col-sm-8">
                                <input id="Cc" v-model="Cc"
                                       :class="errors.Cc ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Cc"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Cc"
                                                    :errorMessages="errors.Cc"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Bcc">
                                Bcc
                            </label>
                            <div class="col-sm-8">
                                <input id="Bcc" v-model="Bcc"
                                       :class="errors.Bcc ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Bcc"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Bcc"
                                                    :errorMessages="errors.Bcc"></InputErrorMessages>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Send To</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SendToCompany">
                                Company
                            </label>
                            <div class="col-sm-8">
                                <Select id="SendToCompany" v-model="SendToCompany" :options="booleanOptions"
                                        :required="false"
                                        :select-class="errors.SendToCompany ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SendToCompany"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SendToCompany"
                                                    :errorMessages="errors.SendToCompany"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SendToUser">
                                User
                            </label>
                            <div class="col-sm-8">
                                <Select id="SendToUser" v-model="SendToUser" :options="booleanOptions"
                                        :required="false"
                                        :select-class="errors.SendToUser ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SendToUser"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SendToUser"
                                                    :errorMessages="errors.SendToUser"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SendToCustomer">
                                Customer
                            </label>
                            <div class="col-sm-8">
                                <Select id="SendToCustomer" v-model="SendToCustomer" :options="booleanOptions"
                                        :required="false"
                                        :select-class="errors.SendToCustomer ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SendToCustomer"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SendToCustomer"
                                                    :errorMessages="errors.SendToCustomer"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SendToSupplier">
                                Supplier
                            </label>
                            <div class="col-sm-8">
                                <Select id="SendToSupplier" v-model="SendToSupplier" :options="booleanOptions"
                                        :required="false"
                                        :select-class="errors.SendToSupplier ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SendToSupplier"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SendToSupplier"
                                                    :errorMessages="errors.SendToSupplier"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Apply To</h5>
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
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Module"
                                                    :errorMessages="errors.Module"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 space-y-2">
                        <h5 class="fw-light text-center">Mail</h5>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Subject">
                                Subject
                            </label>
                            <div class="col-sm-10">
                                <input id="Subject" v-model="Subject"
                                       :class="errors.Subject ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Subject"
                                       required
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Subject"
                                                    :errorMessages="errors.Subject"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Body">
                                Body
                            </label>
                            <div class="col-sm-10">
                            <textarea id="Body" v-model="Body" :class="{'is-invalid': errors.Body}"
                                      class="form-control" rows="4">
                            </textarea>
                                <InputErrorMessages v-if="errors.Body"
                                                    :errorMessages="errors.Body"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-10">
                            <textarea id="Description" v-model="Description" :class="{'is-invalid': errors.Description}"
                                      class="form-control" rows="1">
                            </textarea>
                                <InputErrorMessages v-if="errors.Description"
                                                    :errorMessages="errors.Description"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label col-form-label-sm" for="TemplatePath">
                                TemplatePath
                            </label>
                            <div class="col-sm-10">
                                <input id="TemplatePath" v-model="TemplatePath"
                                       :class="errors.TemplatePath ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="TemplatePath"
                                       required
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.TemplatePath"
                                                    :errorMessages="errors.TemplatePath"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>

        </BaseBlock>

    </div>
</template>
