<script setup>
import {onMounted, ref, watch} from "vue";
import {booleanOptions, cultureOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import VueSelect from 'vue-select';
import Role from "@/models/Office/Role";
import User from "@/models/Office/User";
import {useFormErrors} from "@/composables/useFormErrors";


const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

const {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Email = ref('');
let PhoneNo = ref('');
let MobileNo = ref('');
let Disabled = ref(0);

let Password = ref('');
let PasswordConfirmation = ref('');

let CultureName = ref('da-DK');
let Initials = ref('');
let Territory = ref('');
let Commission = ref(0);
let Billable = ref(1);
let Note = ref('');

let RoleIds = ref([]);

let RoleOptions = ref([]);

let SendMail = ref(1);

const createUserRef = ref(null);

async function getRoles() {
    let {data} = await Role.getRolesByCompany(companyStore.selectedCompany.Id);
    RoleOptions.value = data;
}

async function createUser() {
    createUserRef.value.statusLoading();

    let formData = {
        Name: Name.value,
        Email: Email.value,
        PhoneNo: PhoneNo.value,
        MobileNo: MobileNo.value,
        Disabled: Disabled.value,
        CultureName: CultureName.value,

        Initials: Initials.value,
        Territory: Territory.value,
        Commission: Commission.value,
        Billable: Billable.value,
        Note: Note.value,

        RoleIds: RoleIds.value,
        Password: Password.value,
        Password_confirmation: PasswordConfirmation.value,
        SendMail: SendMail.value
    };

    try {
        let {data, message} = await User.createCompanyUser(companyStore.selectedCompany.Id, formData);
        await router.push({name: 'users'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    createUserRef.value.statusNormal();
}

onMounted(async () => {
    createUserRef.value.statusLoading();
    await getRoles();
    createUserRef.value.statusNormal();
});

function resetForm() {
    Name.value = '';
    Email.value = '';
    PhoneNo.value = '';
    MobileNo.value = '';
    Disabled.value = false;
    Password.value = '';
    PasswordConfirmation.value = '';
    CultureName.value = 'da-DK';
    Initials.value = '';
    Territory.value = '';
    Commission.value = 0;
    Billable.value = '1';
    Note.value = '';
    RoleIds.value = [];
    SendMail.value = '1';
}

watch(() => companyStore.getSelectedCompany, async () => {
    createUserRef.value.statusLoading();
    await getRoles();
    resetErrors();
    resetForm();
    createUserRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="createUserRef" content-full title="Create User">

            <form class="space-y-4" @submit.prevent="createUser">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light">General</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Name"
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
                                       class="form-control" name="Email"
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
                        <h5 class="fw-light">Setting ({{ companyStore.selectedCompany.Name }})</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Initials">
                                Initials<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Initials" v-model="Initials"
                                       :class="errors.Initials ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Initials"
                                       required
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Initials"
                                                    :errorMessages="errors.Initials"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="CultureName">
                                Culture<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="CultureName" :options="cultureOptions"
                                        :required="true"
                                        :select-class="errors.CultureName ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="CultureName"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.CultureName"
                                                    :errorMessages="errors.CultureName"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Territory">
                                Territory
                            </label>
                            <div class="col-sm-8">
                                <input id="Territory" v-model="Territory"
                                       :class="errors.Territory ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Territory"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Territory"
                                                    :errorMessages="errors.Territory"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Commission">
                                Commission<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Commission" v-model.number="Commission"
                                       :class="errors.Commission ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Commission"
                                       required
                                       step="any"
                                       type="number" @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Commission"
                                                    :errorMessages="errors.Commission"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Billable">
                                Billable<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Billable" v-model="Billable" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Billable ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Billable"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Billable"
                                                    :errorMessages="errors.Billable"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Note">
                                Note
                            </label>
                            <div class="col-sm-8">
                                <textarea id="Note" v-model="Note"
                                          :class="errors.Note ? `is-invalid form-control-sm` : `form-control-sm`"
                                          class="form-control" name="Note"
                                          @keyup="resetErrors"
                                ></textarea>
                                <InputErrorMessages v-if="errors.Note"
                                                    :errorMessages="errors.Note"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Roles">
                                Roles
                            </label>
                            <div class="col-sm-8">
                                <VueSelect
                                    v-model="RoleIds"
                                    :class="{'is-invalid': errors.RoleIds}"
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
                                    @option:selected="resetErrors"
                                    @option:deselected="resetErrors"
                                >
                                </VueSelect>
                                <InputErrorMessages v-if="errors.RoleIds"
                                                    :errorMessages="errors.RoleIds"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light">Password</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Password">
                                Password
                            </label>
                            <div class="col-sm-8">
                                <input id="Password" v-model="Password"
                                       :class="errors.Password ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Password"
                                       required
                                       type="password"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Password"
                                                    :errorMessages="errors.Password"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="PasswordConfirmation">
                                Confirm Password
                            </label>
                            <div class="col-sm-8">
                                <input id="PasswordConfirmation" v-model="PasswordConfirmation"
                                       :class="errors.PasswordConfirmation ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="PasswordConfirmation"
                                       required
                                       type="password"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.PasswordConfirmation"
                                                    :errorMessages="errors.PasswordConfirmation"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SendMail">
                                Send Email
                            </label>
                            <div class="col-sm-8">
                                <Select id="SendMail" v-model="SendMail" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.SendMail ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SendMail"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SendMail"
                                                    :errorMessages="errors.SendMail"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>

        </BaseBlock>

    </div>
</template>
