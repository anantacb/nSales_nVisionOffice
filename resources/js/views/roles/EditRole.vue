<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Role from "@/models/Office/Role";
import {useRoute} from "vue-router";

const route = useRoute();

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();


const editRoleRef = ref(null);

async function updateRole() {
    editRoleRef.value.statusLoading();

    let formData = {
        Id: RoleModel.value.Id,
        Name: RoleModel.value.Name,
        Type: RoleModel.value.Type,
        Description: RoleModel.value.Description,
        CompanyId: RoleModel.value.CompanyId
    };

    try {
        let {data, message} = await Role.update(formData);
        editRoleRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        editRoleRef.value.statusNormal();
    }

}

onMounted(async () => {
    editRoleRef.value.statusLoading();
    await getRoleDetails();
    editRoleRef.value.statusNormal();
});

let RoleModel = ref({});

async function getRoleDetails() {
    let {data} = await Role.details(route.params.id);
    RoleModel.value = data;
}

</script>

<template>
    <div class="content">

        <BaseBlock ref="editRoleRef" content-full title="Edit Role">

            <template #options>
                <router-link :to="{name:'roles'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateRole">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="RoleModel.Name"
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
                                <input id="Type" v-model="RoleModel.Type"
                                       :class="errors.Type ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       disabled
                                       name="Type"
                                       required
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Description" v-model="RoleModel.Description"
                                      :class="{'is-invalid': errors.Description}"
                                      class="form-control" rows="4">
                            </textarea>
                                <InputErrorMessages v-if="errors.Description"
                                                    :errorMessages="errors.Description"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Update</button>

            </form>

        </BaseBlock>

    </div>
</template>
