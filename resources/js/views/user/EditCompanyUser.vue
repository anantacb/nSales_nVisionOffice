<script setup>
import {onMounted, ref, watch} from "vue";
import {booleanOptions, cultureOptions, licenceTypeOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import VueSelect from 'vue-select';
import Role from "@/models/Office/Role";
import User from "@/models/Office/User";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import _ from "lodash";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
const {errors, setErrors, resetErrors} = useFormErrors();
const route = useRoute();

let UserId = ref(null);
let Name = ref('');
let Email = ref('');
let PhoneNo = ref('');
let MobileNo = ref('');

let CompanyUserId = ref(null);
let Initials = ref('');
let LicenceType = ref('NvisionMobile');
let CultureName = ref('da-DK');
let Territory = ref('');
let Commission = ref(0);
let Billable = ref(1);
let Note = ref('');

let RoleIds = ref([]);

let RoleOptions = ref([]);


let Devices = ref([]);

const updateCompanyUserRef = ref(null);

async function getRoles() {
    let {data} = await Role.getRolesByCompany(companyStore.selectedCompany.Id);
    RoleOptions.value = data;
}

async function updateUser() {
    updateCompanyUserRef.value.statusLoading();

    let formData = {
        UserId: UserId.value,
        CompanyUserId: CompanyUserId.value,
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
        let {message} = await User.updateCompanyUser(companyStore.selectedCompany.Id, formData);
        updateCompanyUserRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        updateCompanyUserRef.value.statusNormal();
        setErrors(error.response.data.errors);
    }
}

onMounted(async () => {
    updateCompanyUserRef.value.statusLoading();
    await getRoles();
    await getCompanyUserDetails();
    updateCompanyUserRef.value.statusNormal();
});

async function getCompanyUserDetails() {
    let {data} = await User.getCompanyUserDetails(companyStore.selectedCompany.Id, route.params.id);

    UserId.value = data.Id;
    Name.value = data.Name;
    Email.value = data.Email;
    PhoneNo.value = data.PhoneNo;
    MobileNo.value = data.MobileNo;

    CompanyUserId.value = data.company_user.Id;
    Initials.value = data.company_user.Initials;
    LicenceType.value = data.company_user.LicenceType;
    CultureName.value = data.company_user.CultureName;
    Territory.value = data.company_user.Territory;
    Commission.value = data.company_user.Commission;
    Billable.value = data.company_user.Billable;
    Note.value = data.company_user.Note;

    RoleIds.value = data.company_user.roles.map((role) => {
        return role.Id;
    });

    Devices.value = data.devices;
}

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany, oldSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        if (newSelectedCompany.Id !== oldSelectedCompany.Id) {
            await router.push({name: 'company-users'});
        }
    }
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="updateCompanyUserRef">
            <template #content>
                <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                    <li class="nav-item">
                        <button id="btabs-animated-slideup-home-tab"
                                aria-controls="btabs-animated-slideup-home"
                                aria-selected="true"
                                class="nav-link active"
                                data-bs-target="#edit-company-user"
                                data-bs-toggle="tab"
                                role="tab">Edit Company User
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="btabs-animated-slideup-profile-tab"
                                aria-controls="btabs-animated-slideup-profile"
                                aria-selected="false"
                                class="nav-link"
                                data-bs-target="#company-user-devices"
                                data-bs-toggle="tab"
                                role="tab">Devices
                        </button>
                    </li>
                    <li class="nav-item ms-auto">
                        <router-link :to="{name: 'company-users'}" class="btn btn-sm btn-outline-info me-2 mt-2"
                                     type="button"><i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                        </router-link>
                    </li>
                </ul>
                <div class="block-content tab-content overflow-hidden">
                    <div
                        id="edit-company-user"
                        aria-labelledby="btabs-animated-slideup-home-tab"
                        class="tab-pane fade fade-up show active"
                        role="tabpanel"
                        tabindex="0">
                        <form class="space-y-4" @submit.prevent="updateUser">

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
                                                   autocomplete="off" class="form-control"
                                                   disabled
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
                                                   disabled
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
                                                   class="form-control" disabled
                                                   name="PhoneNo"
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
                                                   class="form-control" disabled
                                                   name="MobileNo"
                                                   type="text"
                                                   @keyup="resetErrors"/>
                                            <InputErrorMessages v-if="errors.MobileNo"
                                                                :errorMessages="errors.MobileNo"></InputErrorMessages>
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
                                        <label class="col-sm-4 col-form-label col-form-label-sm" for="LicenceType">
                                            Licence Type<span class="text-danger">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <Select id="LicenceType" v-model="LicenceType" :options="licenceTypeOptions"
                                                    :required="true"
                                                    :select-class="errors.LicenceType ? `is-invalid form-select-sm` : `form-select-sm`"
                                                    name="LicenceType"
                                                    @change="resetErrors"/>
                                            <InputErrorMessages v-if="errors.LicenceType"
                                                                :errorMessages="errors.LicenceType"></InputErrorMessages>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label col-form-label-sm" for="CultureName">
                                            Culture<span class="text-danger">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <Select id="CultureName" v-model="CultureName" :options="cultureOptions"
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
                            </div>

                            <button class="btn btn-outline-primary btn-sm col-2 mb-3" type="submit">Save</button>

                        </form>

                    </div>
                    <div
                        id="company-user-devices"
                        aria-labelledby="btabs-animated-slideup-profile-tab"
                        class="tab-pane fade fade-up"
                        role="tabpanel"
                        tabindex="0">

                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Number</th>
                                <th>Name</th>
                                <th>OSName</th>
                                <th>OSVersion</th>
                                <th>OSLanguage</th>
                                <th>OSPlatform</th>
                                <th>Install Date</th>
                                <th>Last Synced</th>
                                <th>Disabled</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="Device in Devices">
                                <td>{{ Device.company_device ? Device.company_device.Number : '' }}</td>
                                <td>{{ Device.Name }}</td>
                                <td>{{ Device.OSName }}</td>
                                <td>{{ Device.OSVersion }}</td>
                                <td>{{ Device.OSLanguage }}</td>
                                <td>{{ Device.OSPlatform }}</td>
                                <td>{{ Device.InsertTime }}</td>
                                <td>{{ Device.LastWebServiceConnection }}</td>
                                <td>{{ Device.Disabled === 1 ? "Yes" : "No" }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </BaseBlock>
    </div>
</template>

<style scoped>
:deep(.vs__dropdown-menu) {
    position: relative;
}
</style>
