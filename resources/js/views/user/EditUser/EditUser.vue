<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import User from "@/models/Office/User";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import CompanyUsersTable from "@/views/user/EditUser/CompanyUsersTable.vue";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();
const route = useRoute();

let Id = ref(null);
let Name = ref('');
let Email = ref('');
let PhoneNo = ref('');
let MobileNo = ref('');

let CompanyUsers = ref([]);

const updateUserRef = ref(null);

async function updateUser() {
    updateUserRef.value.statusLoading();

    let formData = {
        Id: Id.value,
        Name: Name.value,
        Email: Email.value,
        PhoneNo: PhoneNo.value,
        MobileNo: MobileNo.value
    };

    console.log(formData);
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

                    <form class="space-y-4" @submit.prevent="updateUser">

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

                        <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
                    </form>

                </BaseBlock>
            </div>

            <div class="col-lg-8">
                <CompanyUsersTable :company-users="CompanyUsers"/>
            </div>
        </div>

    </div>
</template>

<style scoped>
</style>
