<script setup>
import Module from "@/models/Office/Module";
import {onMounted, ref} from "vue";
import {useFormErrors} from "@/composables/useFormErrors";
import {booleanOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import ModuleSetting from "@/models/Office/ModuleSetting";

const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let ModuleId = ref('');
let CoreSetting = ref(0);
let Disabled = ref(0);

let DataType = ref('');
let Options = ref('');
let Readonly = ref(0);
let Visible = ref(1);

let Value = ref('');
let ValueExpression = ref('');
let Note = ref('');

async function createModuleSetting() {
    createModuleSettingRef.value.statusLoading();
    let formData = {
        Name : Name.value,
        ModuleId : ModuleId.value,
        CoreSetting : CoreSetting.value,
        Disabled : Disabled.value,
        DataType : DataType.value,
        Options : Options.value,
        Readonly : Readonly.value,
        Visible : Visible.value,
        Value : Value.value,
        ValueExpression : ValueExpression.value,
        Note : Note.value,
    };
    try {
        let {data, message} = await ModuleSetting.create(formData);
        createModuleSettingRef.value.statusNormal();
        await router.push({name: 'module-settings'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createModuleSettingRef.value.statusNormal();
    }
}

const DataTypeOptions = ['Boolean', 'Double', 'Int32', 'String', 'Enum'];

function selectDataTypeFromDropdown(value) {
    DataType.value = value;
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

let createModuleSettingRef = ref(null);

onMounted(() => {
    getAllModules();
});
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="createModuleSettingRef" content-full title="Create Setting">
            <form class="space-y-4" @submit.prevent="createModuleSetting">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <h5 class="fw-light text-center">General</h5>
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="CoreSetting">
                                CoreSetting<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="CoreSetting" v-model="CoreSetting" :options="booleanOptions"
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
                                <Select id="Disabled" v-model="Disabled" :options="booleanOptions"
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
                                        v-model="DataType"
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
                                </div>
                                <InputErrorMessages v-if="errors.DataType"
                                                    :errorMessages="errors.DataType"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Options">
                                Options
                            </label>
                            <div class="col-sm-8">
                                <input id="Options" v-model="Options"
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
                                <Select id="Readonly" v-model="Readonly" :options="booleanOptions"
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
                                <Select id="Visible" v-model="Visible" :options="booleanOptions"
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
                            <textarea id="Value" v-model="Value" :class="{'is-invalid': errors.Value}"
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
                            <textarea id="ValueExpression" v-model="ValueExpression"
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
                            <textarea id="Note" v-model="Note" :class="{'is-invalid': errors.Note}"
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
