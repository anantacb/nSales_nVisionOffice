<script setup>
import {onMounted, ref} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import TableHelper from "@/models/TableHelper";
import DefaultRole from "@/models/Office/DefaultRole";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Type = ref('Client');
let Description = ref('');

const createDefaultRoleRef = ref(null);
let TypeOptions = ref([]);

async function getRoleTypes() {
    const {data: RoleTypeData} = await TableHelper.getEnumValues('Office', 'Role', 'Type');
    TypeOptions.value = RoleTypeData.map((item) => ({label: item, value: item}));
}

async function createDefaultRole() {
    createDefaultRoleRef.value.statusLoading();
    const formData = {
        Name: Name.value,
        Type: Type.value,
        Description: Description.value,
    };
    try {
        const {message} = await DefaultRole.create(formData);
        createDefaultRoleRef.value.statusNormal();
        await router.push({name: 'default-roles'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createDefaultRoleRef.value.statusNormal();
    }
}

onMounted(async () => {
    createDefaultRoleRef.value.statusLoading();
    await getRoleTypes();
    createDefaultRoleRef.value.statusNormal();
});
</script>

<template>
    <div class="content">
        <BaseBlock ref="createDefaultRoleRef" content-full title="Create Default Role">

            <template #options>
                <router-link :to="{name:'default-roles'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createDefaultRole">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Description" v-model="Description"
                                      :class="{'is-invalid': errors.Description}"
                                      class="form-control" rows="4">
                            </textarea>
                                <InputErrorMessages v-if="errors.Description"
                                                    :errorMessages="errors.Description"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
            </form>
        </BaseBlock>
    </div>
</template>
