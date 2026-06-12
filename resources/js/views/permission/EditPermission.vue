<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Permission from "@/models/Office/Permission";
import Module from "@/models/Office/Module";
import {useRoute} from "vue-router";

const route = useRoute();

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

const editPermissionRef = ref(null);

let PermissionModel = ref({});

let ModuleOptions = ref([{label: '— None —', value: ''}]);
const DeveloperOnlyOptions = [
    {label: 'No', value: 0},
    {label: 'Yes', value: 1}
];

async function getModules() {
    let {data} = await Module.getAllModules();
    ModuleOptions.value = [
        {label: '— None —', value: ''},
        ...data.map((item) => ({label: item.Name, value: item.Id}))
    ];
}

async function getPermissionDetails() {
    let {data} = await Permission.details(route.params.id);
    PermissionModel.value = {
        ...data,
        ModuleId: data.ModuleId ?? '',
        IsDeveloperOnly: Number(data.IsDeveloperOnly)
    };
}

async function updatePermission() {
    editPermissionRef.value.statusLoading();

    let formData = {
        Id: PermissionModel.value.Id,
        Aliases: PermissionModel.value.Aliases,
        Name: PermissionModel.value.Name,
        ModuleId: PermissionModel.value.ModuleId === '' ? null : PermissionModel.value.ModuleId,
        Description: PermissionModel.value.Description,
        IsDeveloperOnly: PermissionModel.value.IsDeveloperOnly
    };

    try {
        let {message} = await Permission.update(formData);
        editPermissionRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        editPermissionRef.value.statusNormal();
    }
}

onMounted(async () => {
    editPermissionRef.value.statusLoading();
    await getModules();
    await getPermissionDetails();
    editPermissionRef.value.statusNormal();
});
</script>

<template>
    <div class="content">

        <BaseBlock ref="editPermissionRef" content-full title="Edit Permission">

            <template #options>
                <router-link :to="{name:'permissions'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updatePermission">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Aliases">
                                Aliases (slug)<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Aliases" v-model="PermissionModel.Aliases"
                                       :class="errors.Aliases ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Aliases"
                                       required type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Aliases"
                                                    :errorMessages="errors.Aliases"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Action (Name)<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="PermissionModel.Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Name"
                                       required type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ModuleId">
                                Module
                            </label>
                            <div class="col-sm-8">
                                <Select id="ModuleId" v-model="PermissionModel.ModuleId" :options="ModuleOptions"
                                        :select-class="errors.ModuleId ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ModuleId"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.ModuleId"
                                                    :errorMessages="errors.ModuleId"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="IsDeveloperOnly">
                                Developer Only<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="IsDeveloperOnly" v-model="PermissionModel.IsDeveloperOnly"
                                        :options="DeveloperOnlyOptions"
                                        :required="true"
                                        :select-class="errors.IsDeveloperOnly ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="IsDeveloperOnly"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.IsDeveloperOnly"
                                                    :errorMessages="errors.IsDeveloperOnly"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-8">
                                <textarea id="Description" v-model="PermissionModel.Description"
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
