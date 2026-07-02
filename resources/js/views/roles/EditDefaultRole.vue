<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import DefaultRole from "@/models/Office/DefaultRole";
import RolePermissionsBlock from "@/components/roles/RolePermissionsBlock.vue";
import {useRoute} from "vue-router";

const route = useRoute();

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

const editDefaultRoleRef = ref(null);

let RoleModel = ref({});

async function updateDefaultRole() {
    editDefaultRoleRef.value.statusLoading();
    const formData = {
        Id: RoleModel.value.Id,
        Name: RoleModel.value.Name,
        Description: RoleModel.value.Description,
    };
    try {
        const {message} = await DefaultRole.update(formData);
        editDefaultRoleRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        editDefaultRoleRef.value.statusNormal();
    }
}

async function getRoleDetails() {
    const {data} = await DefaultRole.details(route.params.id);
    RoleModel.value = data ?? {};
}

onMounted(async () => {
    editDefaultRoleRef.value.statusLoading();
    await getRoleDetails();
    editDefaultRoleRef.value.statusNormal();
});
</script>

<template>
    <div class="content">
        <div class="row">
            <div class="col-lg-4">

        <BaseBlock ref="editDefaultRoleRef" content-full title="Edit Default Role">

            <template #options>
                <router-link :to="{name:'default-roles'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateDefaultRole">
                <div class="row">
                    <div class="col-12 space-y-2">
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
                                       class="form-control form-control-sm"
                                       disabled
                                       name="Type"
                                       type="text"/>
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
            <div class="col-lg-8">
                <RolePermissionsBlock :is-default-role="true" :role-id="RoleModel.Id" :role-type="RoleModel.Type"/>
            </div>
        </div>

    </div>
</template>
