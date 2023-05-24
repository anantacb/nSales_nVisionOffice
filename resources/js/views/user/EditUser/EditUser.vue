<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import CompanyUsersTable from "@/views/user/EditUser/CompanyUsersTable.vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import {booleanOptions, cultureOptions, licenceTypeOptions} from "@/data/dropDownOptions";
import VueSelect from "vue-select";
import Company from "@/models/Office/Company";
import Role from "@/models/Office/Role";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();
const {errors: errorsAssignForm, setErrors: setErrorsAssignForm, resetErrors: resetErrorsAssignForm} = useFormErrors();

const route = useRoute();

let Id = ref(null);
let Name = ref('');
let Email = ref('');
let PhoneNo = ref('');
let MobileNo = ref('');

let CompanyUsers = ref([]);

const updateUserRef = ref(null);
const modal = ref(null);

async function updateUser() {
    updateUserRef.value.statusLoading();

    let formData = {
        Id: Id.value,
        Name: Name.value,
        Email: Email.value,
        PhoneNo: PhoneNo.value,
        MobileNo: MobileNo.value
    };

    try {
        let {message} = await User.update(formData);
        updateUserRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        updateUserRef.value.statusNormal();
        setErrors(error.response.data.errors);
    }
}

onMounted(async () => {
    updateUserRef.value.statusLoading();
    await getUserDetails();
    await getAssignAbleCompanies();
    updateUserRef.value.statusNormal();
});

async function getUserDetails() {
    let {data} = await User.details(route.params.id);

    Id.value = data.Id;
    Name.value = data.Name;
    Email.value = data.Email;
    PhoneNo.value = data.PhoneNo;
    MobileNo.value = data.MobileNo;

    CompanyUsers.value = data.company_users;
}

function previewAssignToCompanyForm() {
    modal.value.openModal();
}


let Initials = ref('');
let LicenceType = ref('NvisionMobile');
let CultureName = ref('da-DK');
let Territory = ref('');
let Commission = ref(0);
let Billable = ref(1);
let Note = ref('');

let RoleIds = ref([]);

let RoleOptions = ref([]);

let CompanyId = ref('');
let CompanyOptions = ref([
    {
        label: 'Select Company',
        value: ''
    }
]);

const assignToCompanyRef = ref(null);

async function assignUserToCompany() {
    assignToCompanyRef.value.statusLoading();

    let formData = {
        UserId: Id.value,
        Initials: Initials.value,
        LicenceType: LicenceType.value,
        CultureName: CultureName.value,
        Territory: Territory.value,
        Commission: Commission.value,
        Billable: Billable.value,
        Note: Note.value,

        RoleIds: RoleIds.value
    };

    try {
        let {message} = await User.assignUserToCompany(CompanyId.value, formData);
        assignToCompanyRef.value.statusNormal();
        modal.value.closeModal();
        notificationStore.showNotification(message);
        updateUserRef.value.statusLoading();
        await getUserDetails();
        await getAssignAbleCompanies();
        resetAssignUserToCompanyForm();
    } catch (error) {
        setErrorsAssignForm(error.response.data.errors);
        assignToCompanyRef.value.statusNormal();
    }
}

async function getAssignAbleCompanies() {
    let {data, message} = await Company.getAssignableCompaniesByUser(Id.value);

    CompanyOptions.value = [
        {
            label: 'Select Company',
            value: ''
        }
    ];

    data.map((company) => {
        CompanyOptions.value.push({
            label: company.Name,
            value: company.Id
        })
    });
}

async function getRoles() {
    RoleIds.value = [];
    RoleOptions.value = [];

    if (!CompanyId.value) {
        return;
    }

    let {data} = await Role.getRolesByCompany(CompanyId.value);
    RoleOptions.value = data;
}

function resetAssignUserToCompanyForm() {
    Initials.value = '';
    LicenceType.value = 'NvisionMobile';
    CultureName.value = 'da-DK';
    Territory.value = '';
    Commission.value = 0;
    Billable.value = 1;
    Note.value = '';

    RoleIds.value = [];
}

</script>

<template>
    <div class="content">

        <div class="row">
            <div class="col-lg-4">
                <BaseBlock ref="updateUserRef" content-full title="Edit User">
                    <template #options>
                        <router-link :to="{name:'users'}" class="btn btn-sm btn-outline-info">
                            <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                        </router-link>
                    </template>

                    <form class="space-y-2" @submit.prevent="updateUser">

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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Email">
                                Email<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Email" v-model="Email"
                                       :class="errors.Email ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Email"
                                       required
                                       type="email"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Email"
                                                    :errorMessages="errors.Email"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="PhoneNo">
                                Phone No
                            </label>
                            <div class="col-sm-8">
                                <input id="PhoneNo" v-model="PhoneNo"
                                       :class="errors.PhoneNo ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="PhoneNo"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.PhoneNo"
                                                    :errorMessages="errors.PhoneNo"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MobileNo">
                                Mobile No
                            </label>
                            <div class="col-sm-8">
                                <input id="MobileNo" v-model="MobileNo"
                                       :class="errors.MobileNo ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MobileNo"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MobileNo"
                                                    :errorMessages="errors.MobileNo"></InputErrorMessages>
                            </div>
                        </div>

                        <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
                    </form>

                </BaseBlock>
            </div>

            <div class="col-lg-8">
                <CompanyUsersTable :company-users="CompanyUsers"
                                   @assignToCompany="previewAssignToCompanyForm"
                />
            </div>
        </div>

        <ModalComponent id="assigneeCompanyFormModal" ref="modal">
            <template v-slot:modal-content>
                <BaseBlock ref="assignToCompanyRef" class="mb-0" title="Assign To Company" transparent>
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
                        <form @submit.prevent="assignUserToCompany">
                            <div class="block-content fs-sm space-y-2 mb-2">

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="CompanyId">
                                        Company<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="CompanyId" v-model="CompanyId" :options="CompanyOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.CompanyId ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="CompanyId"
                                                @change="getRoles();resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.CompanyId"
                                                            :errorMessages="errorsAssignForm.CompanyId"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Initials">
                                        Initials<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Initials" v-model="Initials"
                                               :class="errorsAssignForm.Initials ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Initials"
                                               required
                                               type="text"
                                               @keyup="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.Initials"
                                                            :errorMessages="errorsAssignForm.Initials"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="LicenceType">
                                        Licence Type<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="LicenceType" v-model="LicenceType" :options="licenceTypeOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.LicenceType ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="LicenceType"
                                                @change="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.LicenceType"
                                                            :errorMessages="errorsAssignForm.LicenceType"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="CultureName">
                                        Culture<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="CultureName" v-model="CultureName" :options="cultureOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.CultureName ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="CultureName"
                                                @change="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.CultureName"
                                                            :errorMessages="errorsAssignForm.CultureName"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Territory">
                                        Territory
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Territory" v-model="Territory"
                                               :class="errorsAssignForm.Territory ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Territory"
                                               type="text"
                                               @keyup="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.Territory"
                                                            :errorMessages="errorsAssignForm.Territory"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Commission">
                                        Commission<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Commission" v-model.number="Commission"
                                               :class="errorsAssignForm.Commission ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Commission"
                                               required
                                               step="any"
                                               type="number" @keyup="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.Commission"
                                                            :errorMessages="errorsAssignForm.Commission"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Billable">
                                        Billable<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="Billable" v-model="Billable" :options="booleanOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.Billable ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="Billable"
                                                @change="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.Billable"
                                                            :errorMessages="errorsAssignForm.Billable"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Note">
                                        Note
                                    </label>
                                    <div class="col-sm-8">
                                <textarea id="Note" v-model="Note"
                                          :class="errorsAssignForm.Note ? `is-invalid form-control-sm` : `form-control-sm`"
                                          class="form-control" name="Note"
                                          @keyup="resetErrorsAssignForm"
                                ></textarea>
                                        <InputErrorMessages v-if="errorsAssignForm.Note"
                                                            :errorMessages="errorsAssignForm.Note"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Roles">
                                        Roles
                                    </label>
                                    <div class="col-sm-8">
                                        <VueSelect
                                            v-model="RoleIds"
                                            :class="{'is-invalid': errorsAssignForm.RoleIds}"
                                            :clearable="true"
                                            :get-option-label="role => role.Name"
                                            :inputId="`Roles`"
                                            :loading="false"
                                            :options="RoleOptions"
                                            :reduce="role => role.Id"
                                            :searchable="true"
                                            multiple
                                            placeholder="Select Roles"
                                            required
                                            @option:selected="resetErrorsAssignForm"
                                            @option:deselected="resetErrorsAssignForm"
                                        >
                                        </VueSelect>
                                        <InputErrorMessages v-if="errorsAssignForm.RoleIds"
                                                            :errorMessages="errorsAssignForm.RoleIds"></InputErrorMessages>
                                    </div>
                                </div>


                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                                <button
                                    class="btn btn-sm btn-primary"
                                    type="submit"
                                >
                                    Save
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
</template>

<style scoped>
</style>
