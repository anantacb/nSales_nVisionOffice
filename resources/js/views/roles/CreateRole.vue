<script setup>
import {onMounted, ref} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import TableHelper from "@/models/TableHelper";
import Role from "@/models/Office/Role";
import {useCompanyStore} from "@/stores/companyStore";

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
const {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Type = ref('Client');
let Description = ref('');


const createRoleRef = ref(null);

let TypeOptions = ref([]);

async function getRoleTypes() {
    let {data: RoleTypeData} = await TableHelper.getEnumValues('Office', `Role`, 'Type');
    TypeOptions.value = RoleTypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

async function createRole() {
    createRoleRef.value.statusLoading();

    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        Name: Name.value,
        Type: Type.value,
        Description: Description.value
    };

    try {
        let {data, message} = await Role.create(formData);
        createRoleRef.value.statusNormal();
        await router.push({name: 'roles'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createRoleRef.value.statusNormal();
    }
}

onMounted(async () => {
    createRoleRef.value.statusLoading();
    await getRoleTypes();
    createRoleRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="createRoleRef" :title="`Create Role for (${companyStore.selectedCompany.Name})`" content-full>

            <template #options>
                <router-link :to="{name:'roles'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createRole">

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
                            <textarea id="Description" v-model="Description" :class="{'is-invalid': errors.Description}"
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
