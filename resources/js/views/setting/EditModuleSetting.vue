<script setup>
import Module from "@/models/Office/Module";
import {onMounted, ref} from "vue";
import {useFormErrors} from "@/composables/useFormErrors";
import {booleanOptions} from "@/data/dropDownOptions";
import {useNotificationStore} from "@/stores/notificationStore";
import ModuleSetting from "@/models/Office/ModuleSetting";
import {useRoute} from "vue-router";

const route = useRoute();
const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let ModuleSettingModel = ref({});

async function updateModuleSetting() {
    updateModuleSettingRef.value.statusLoading();
    let formData = {
        Id: ModuleSettingModel.value.Id,
        Name: ModuleSettingModel.value.Name,
        ModuleId: ModuleSettingModel.value.ModuleId,
        CoreSetting: ModuleSettingModel.value.CoreSetting,
        Disabled: ModuleSettingModel.value.Disabled,
        DataType: ModuleSettingModel.value.DataType,
        Options: ModuleSettingModel.value.Options,
        Readonly: ModuleSettingModel.value.Readonly,
        Visible: ModuleSettingModel.value.Visible,
        Value: ModuleSettingModel.value.Value,
        ValueExpression: ModuleSettingModel.value.ValueExpression,
        Note: ModuleSettingModel.value.Note,
    };
    try {
        let {data, message} = await ModuleSetting.update(formData);
        updateModuleSettingRef.value.statusNormal();
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        updateModuleSettingRef.value.statusNormal();
    }
}

const DataTypeOptions = ['Boolean', 'Double', 'Int32', 'String', 'Enum'];

function selectDataTypeFromDropdown(value) {
    ModuleSettingModel.value.DataType = value;
}

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

let updateModuleSettingRef = ref(null);

onMounted(async () => {
    updateModuleSettingRef.value.statusLoading();
    await getAllModules();
    await getModuleSettingDetails();
    updateModuleSettingRef.value.statusNormal();
});

async function getModuleSettingDetails() {
    let {data} = await ModuleSetting.details(route.params.id);
    ModuleSettingModel.value = data;
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="updateModuleSettingRef" content-full title="Edit Setting">
            <template #options>
                <router-link :to="{name:'settings'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="updateModuleSetting">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">General</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="ModuleSettingModel.Name"
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Module">
                                Module<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Module" v-model="ModuleSettingModel.ModuleId" :options="ModuleOptions"
                                        :required="true"
                                        :select-class="errors.Module ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Module"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Module"
                                                    :errorMessages="errors.Module"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="CoreSetting">
                                CoreSetting<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="CoreSetting" v-model="ModuleSettingModel.CoreSetting"
                                        :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.CoreSetting ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="CoreSetting"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.CoreSetting"
                                                    :errorMessages="errors.CoreSetting"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Disabled">
                                Disabled<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Disabled" v-model="ModuleSettingModel.Disabled" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Disabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Disabled"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Disabled"
                                                    :errorMessages="errors.Disabled"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Setting</h5>
                        <div class="row">
                            <label class="col-sm-3 col-form-label col-form-label-sm" for="DataType">
                                DataType<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-1">
                                <PopOverButton
                                    btnClass="btn btn-sm"
                                    content="enums must be formatted like: enum('A','B',...)."
                                    iconClass="si si-info">
                                </PopOverButton>
                            </div>
                            <div class="col-sm-8">
                                <div class="input-group input-group-sm">
                                    <input
                                        id="DataType"
                                        v-model="ModuleSettingModel.DataType"
                                        :class="errors.DataType ? `is-invalid` : ``"
                                        aria-label="Select Datatype"
                                        class="form-control"
                                        required
                                        type="text"
                                    />
                                    <button
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"
                                        type="button"
                                    >
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a v-for="DataTypeOption in DataTypeOptions" class="dropdown-item"
                                           @click="selectDataTypeFromDropdown(DataTypeOption)">
                                            {{ DataTypeOption }}
                                        </a>
                                    </div>

                                    <InputErrorMessages v-if="errors.DataType"
                                                        :errorMessages="errors.DataType"></InputErrorMessages>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Options">
                                Options
                            </label>
                            <div class="col-sm-8">
                                <input id="Options" v-model="ModuleSettingModel.Options"
                                       :class="errors.Options ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Options"
                                       type="text"
                                       @keyup="resetErrors"/>
                                <InputErrorMessages v-if="errors.Options"
                                                    :errorMessages="errors.Options"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Readonly">
                                Readonly<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Readonly" v-model="ModuleSettingModel.Readonly" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Readonly ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Readonly"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Readonly"
                                                    :errorMessages="errors.Readonly"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Visible">
                                Visible<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Visible" v-model="ModuleSettingModel.Visible" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Visible ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Visible"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.Visible"
                                                    :errorMessages="errors.Visible"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">Value</h5>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Value">
                                Value
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Value" v-model="ModuleSettingModel.Value"
                                      :class="{'is-invalid': errors.Value}"
                                      class="form-control form-control-sm" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.Value"
                                                    :errorMessages="errors.Value"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ValueExpression">
                                ValueExpression
                            </label>
                            <div class="col-sm-8">
                            <textarea id="ValueExpression" v-model="ModuleSettingModel.ValueExpression"
                                      :class="{'is-invalid': errors.ValueExpression}"
                                      class="form-control form-control-sm" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.ValueExpression"
                                                    :errorMessages="errors.ValueExpression"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Note">
                                Note
                            </label>
                            <div class="col-sm-8">
                            <textarea id="Note" v-model="ModuleSettingModel.Note" :class="{'is-invalid': errors.Note}"
                                      class="form-control form-control-sm" rows="2">
                            </textarea>
                                <InputErrorMessages v-if="errors.Note"
                                                    :errorMessages="errors.Note"></InputErrorMessages>
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
