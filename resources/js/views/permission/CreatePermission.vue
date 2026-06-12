<script setup>
import {onMounted, ref} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Permission from "@/models/Office/Permission";
import Module from "@/models/Office/Module";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

let Aliases = ref('');
let Name = ref('');
let ModuleId = ref('');
let Description = ref('');
let IsDeveloperOnly = ref(0);

const createPermissionRef = ref(null);

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

async function createPermission() {
    createPermissionRef.value.statusLoading();

    let formData = {
        Aliases: Aliases.value,
        Name: Name.value,
        ModuleId: ModuleId.value === '' ? null : ModuleId.value,
        Description: Description.value,
        IsDeveloperOnly: IsDeveloperOnly.value
    };

    try {
        let {message} = await Permission.create(formData);
        createPermissionRef.value.statusNormal();
        await router.push({name: 'permissions'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createPermissionRef.value.statusNormal();
    }
}

onMounted(async () => {
    createPermissionRef.value.statusLoading();
    await getModules();
    createPermissionRef.value.statusNormal();
});
</script>

<template>
    <div class="content">

        <BaseBlock ref="createPermissionRef" content-full title="Create Permission">

            <template #options>
                <router-link :to="{name:'permissions'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createPermission">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Aliases">
                                Aliases (slug)<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Aliases" v-model="Aliases"
                                       :class="errors.Aliases ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Aliases" placeholder="e.g. Flag.Manage"
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
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control"
                                       name="Name" placeholder="e.g. Manage"
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
                                <Select id="ModuleId" v-model="ModuleId" :options="ModuleOptions"
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
                                <Select id="IsDeveloperOnly" v-model="IsDeveloperOnly" :options="DeveloperOnlyOptions"
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
