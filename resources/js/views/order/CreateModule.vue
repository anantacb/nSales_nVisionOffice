<script setup>
import Module from "@/models/Office/Module";
import {onMounted, ref} from "vue";
import {useFormErrors} from "@/composables/useFormErrors";
import {booleanOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let ModuleId = ref('');
let Name = ref('');
let Type = ref('Standard');
let SyncOfficeData = ref(0);
let MainTableName = ref('');
let Description = ref('');
let Note = ref('');
let Disabled = ref(0);
let ViewPath = ref('');
let IsGenericModule = ref(0);
let MenuVisible = ref(1);
let MenuTitle = ref('');
let MenuSubTitle = ref('');
let MenuGroup = ref('');
let MenuOrder = ref(0);
let MenuIcon = ref('');
let ElementNameSingular = ref('');
let ElementNamePlural = ref('');

async function createModule() {
    createModuleRef.value.statusLoading();
    let formData = {
        ModuleId: ModuleId.value,
        Name: Name.value,
        Type: Type.value,
        SyncOfficeData: SyncOfficeData.value,
        MainTableName: MainTableName.value,
        Description: Description.value,
        Note: Note.value,
        Disabled: Disabled.value,
        ViewPath: ViewPath.value,
        IsGenericModule: IsGenericModule.value,
        MenuVisible: MenuVisible.value,
        MenuTitle: MenuTitle.value,
        MenuSubTitle: MenuSubTitle.value,
        MenuGroup: MenuGroup.value,
        MenuOrder: MenuOrder.value,
        MenuIcon: MenuIcon.value,
        ElementNameSingular: ElementNameSingular.value,
        ElementNamePlural: ElementNamePlural.value,
    };
    try {
        let {data, message} = await Module.create(formData);
        createModuleRef.value.statusNormal();
        await router.push({name: 'modules'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createModuleRef.value.statusNormal();
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

let createModuleRef = ref(null);

onMounted(() => {
    getAllModules();
});
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="createModuleRef" content-full title="Create Module">
            <template #options>
                <router-link :to="{name:'modules'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createModule">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">General</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Module">
                                Module<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Module" v-model="ModuleId" :options="ModuleOptions"
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="MainTableName">
                                MainTableName
                            </label>
                            <div class="col-sm-8">
                                <input id="MainTableName" v-model="MainTableName"
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
                            <textarea id="Description" v-model="Description" :class="{'is-invalid': errors.Description}"
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
                                <Select id="Disabled" v-model="Disabled" :options="booleanOptions"
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
                                <Select id="SyncOfficeData" v-model="SyncOfficeData" :options="booleanOptions"
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
                                <Select id="IsGenericModule" v-model="IsGenericModule" :options="booleanOptions"
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
                                <input id="ViewPath" v-model="ViewPath"
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
                            <textarea id="Note" v-model="Note" :class="{'is-invalid': errors.Note}"
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
                                <Select id="MenuVisible" v-model="MenuVisible" :options="booleanOptions"
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
                                <input id="MenuTitle" v-model="MenuTitle"
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
                                <input id="MenuSubTitle" v-model="MenuSubTitle"
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
                                <input id="MenuGroup" v-model="MenuGroup"
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
                                <input id="MenuOrder" v-model.number="MenuOrder"
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
                                <input id="MenuIcon" v-model="MenuIcon"
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
                                <input id="ElementNameSingular" v-model="ElementNameSingular"
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
                                <input id="ElementNamePlural" v-model="ElementNamePlural"
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
