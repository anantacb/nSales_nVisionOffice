<script setup>
import Module from "@/models/Office/Module";
import {onMounted, ref} from "vue";
import {useFormErrors} from "@/composables/useFormErrors";
import {booleanOptions} from "@/data/dropDownOptions";
import {useNotificationStore} from "@/stores/notificationStore";
import {useRoute} from "vue-router";

const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let ModuleModel = ref({});

async function updateModule() {
    updateModuleRef.value.statusLoading();
    let formData = {
        Id: ModuleModel.value.Id,
        ModuleId: ModuleModel.value.ModuleId,
        Name: ModuleModel.value.Name,
        Type: ModuleModel.value.Type,
        SyncOfficeData: ModuleModel.value.SyncOfficeData,
        MainTableName: ModuleModel.value.MainTableName,
        Description: ModuleModel.value.Description,
        Note: ModuleModel.value.Note,
        Disabled: ModuleModel.value.Disabled,
        ViewPath: ModuleModel.value.ViewPath,
        IsGenericModule: ModuleModel.value.IsGenericModule,
        MenuVisible: ModuleModel.value.MenuVisible,
        MenuTitle: ModuleModel.value.MenuTitle,
        MenuSubTitle: ModuleModel.value.MenuSubTitle,
        MenuGroup: ModuleModel.value.MenuGroup,
        MenuOrder: ModuleModel.value.MenuOrder,
        MenuIcon: ModuleModel.value.MenuIcon,
        ElementNameSingular: ModuleModel.value.ElementNameSingular,
        ElementNamePlural: ModuleModel.value.ElementNamePlural,
    };
    try {
        let {data, message} = await Module.update(formData);
        updateModuleRef.value.statusNormal();
        //await router.push({name: 'modules'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        updateModuleRef.value.statusNormal();
    }
}

const TypeOptions = ['', 'Core', 'Root', 'Package', 'Standard', 'Extension'].map((item) => {
    return {
        label: item ? item : 'Please Select',
        value: item
    }
});

let ModuleOptions = ref([]);

async function getAllModules() {
    let {data} = await Module.getAllModules();
    let options = [{label: 'Select Module', value: ''}];

    data.forEach((module) => {
        let option = {label: module.Name, value: module.Id};
        options.push(option);
    });

    ModuleOptions.value = options;
}

const route = useRoute();

async function getModuleDetails() {
    let {data} = await Module.details(route.params.id);
    ModuleModel.value = data;
}

let updateModuleRef = ref(null);

onMounted(async () => {
    updateModuleRef.value.statusLoading();
    await getModuleDetails();
    await getAllModules();
    updateModuleRef.value.statusNormal();
});
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="updateModuleRef" content-full title="Edit Module">
            <template #options>
                <router-link :to="{name:'modules'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="updateModule">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">General</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Module">
                                Module<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Module" v-model="ModuleModel.ModuleId" :options="ModuleOptions"
                                        :required="true"
                                        :select-class="errors.Module ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Module"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Module"
                                                    :errorMessages="errors.Module"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="ModuleModel.Name"
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
                                <Select id="Type" v-model="ModuleModel.Type" :options="TypeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Type"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MainTableName">
                                MainTableName
                            </label>
                            <div class="col-sm-8">
                                <input id="MainTableName" v-model="ModuleModel.MainTableName"
                                       :class="errors.MainTableName ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MainTableName"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MainTableName"
                                                    :errorMessages="errors.MainTableName"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Description">
                                Description
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Description" v-model="ModuleModel.Description"
                                      :class="{'is-invalid': errors.Description}"
                                      class="form-control form-control-sm" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.Description"
                                                    :errorMessages="errors.Description"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Setting</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Disabled">
                                Disabled<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Disabled" v-model="ModuleModel.Disabled" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Disabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Disabled"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Disabled"
                                                    :errorMessages="errors.Disabled"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SyncOfficeData">
                                SyncOfficeData<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="SyncOfficeData" v-model="ModuleModel.SyncOfficeData"
                                        :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.SyncOfficeData ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="SyncOfficeData"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.SyncOfficeData"
                                                    :errorMessages="errors.SyncOfficeData"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="IsGenericModule">
                                IsGenericModule<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="IsGenericModule" v-model="ModuleModel.IsGenericModule"
                                        :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.IsGenericModule ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="IsGenericModule"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.IsGenericModule"
                                                    :errorMessages="errors.IsGenericModule"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ViewPath">
                                ViewPath
                            </label>
                            <div class="col-sm-8">
                                <input id="ViewPath" v-model="ModuleModel.ViewPath"
                                       :class="errors.ViewPath ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="ViewPath"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.ViewPath"
                                                    :errorMessages="errors.ViewPath"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Note">
                                Note
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Note" v-model="ModuleModel.Note" :class="{'is-invalid': errors.Note}"
                                      class="form-control form-control-sm" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.Note"
                                                    :errorMessages="errors.Note"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Presentation</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuVisible">
                                MenuVisible<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="MenuVisible" v-model="ModuleModel.MenuVisible" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.MenuVisible ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="MenuVisible"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuVisible"
                                                    :errorMessages="errors.MenuVisible"></InputErrorMessages>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuTitle">
                                MenuTitle
                            </label>
                            <div class="col-sm-8">
                                <input id="MenuTitle" v-model="ModuleModel.MenuTitle"
                                       :class="errors.MenuTitle ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MenuTitle"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuTitle"
                                                    :errorMessages="errors.MenuTitle"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuSubTitle">
                                MenuSubTitle
                            </label>
                            <div class="col-sm-8">
                                <input id="MenuSubTitle" v-model="ModuleModel.MenuSubTitle"
                                       :class="errors.MenuSubTitle ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MenuSubTitle"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuSubTitle"
                                                    :errorMessages="errors.MenuSubTitle"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuGroup">
                                MenuGroup
                            </label>
                            <div class="col-sm-8">
                                <input id="MenuGroup" v-model="ModuleModel.MenuGroup"
                                       :class="errors.MenuGroup ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MenuGroup"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuGroup"
                                                    :errorMessages="errors.MenuGroup"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuOrder">
                                MenuOrder
                            </label>
                            <div class="col-sm-8">
                                <input id="MenuOrder" v-model.number="ModuleModel.MenuOrder"
                                       :class="errors.MenuOrder ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MenuOrder"
                                       type="number"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuOrder"
                                                    :errorMessages="errors.MenuOrder"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MenuIcon">
                                MenuIcon
                            </label>
                            <div class="col-sm-8">
                                <input id="MenuIcon" v-model="ModuleModel.MenuIcon"
                                       :class="errors.MenuIcon ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="MenuIcon"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.MenuIcon"
                                                    :errorMessages="errors.MenuIcon"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ElementNameSingular">
                                ElementNameSingular
                            </label>
                            <div class="col-sm-8">
                                <input id="ElementNameSingular" v-model="ModuleModel.ElementNameSingular"
                                       :class="errors.ElementNameSingular ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="ElementNameSingular"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.ElementNameSingular"
                                                    :errorMessages="errors.ElementNameSingular"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ElementNamePlural">
                                ElementNamePlural
                            </label>
                            <div class="col-sm-8">
                                <input id="ElementNamePlural" v-model="ModuleModel.ElementNamePlural"
                                       :class="errors.ElementNamePlural ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="ElementNamePlural"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.ElementNamePlural"
                                                    :errorMessages="errors.ElementNamePlural"></InputErrorMessages>
                            </div>
                        </div>

                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
            </form>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
