<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Application from "@/models/Office/Application";
import ApplicationModulesTable from "@/views/application/EditApplication/ApplicationModulesTable.vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import Module from "@/models/Office/Module";
import {booleanOptions} from "@/data/dropDownOptions";
import ApplicationModule from "@/models/Office/ApplicationModule";

const route = useRoute();
const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();
let {errors: errorsAssignForm, setErrors: setErrorsAssignForm, resetErrors: resetErrorsAssignForm} = useFormErrors();
let {errors: errorsEditForm, setErrors: setErrorsEditForm, resetErrors: resetErrorsEditForm} = useFormErrors();

const updateApplicationRef = ref(null);
const assignModuleToApplicationRef = ref(null);
const ApplicationModel = ref({});
const ApplicationModules = ref([]);
const ApplicationModulesOptions = ref([]);

async function getApplicationDetails() {
    let {data} = await Application.details(route.params.id);
    ApplicationModel.value = data;
    ApplicationModules.value = data.modules;
    ApplicationModulesOptions.value = [
        {
            label: 'Select Module',
            value: ''
        }
    ];
    data.modules.map((module) => {
        ApplicationModulesOptions.value.push({
            label: module.Name,
            value: module.Id
        })
    });
    data
}

let AssignableModuleOptions = ref([]);

async function getAssignableModulesToApplication() {
    let {data, message} = await Module.getAssignableModulesByApplication(route.params.id);
    AssignableModuleOptions.value = [
        {
            label: 'Select Module',
            value: ''
        }
    ];
    data.map((module) => {
        AssignableModuleOptions.value.push({
            label: module.Name,
            value: module.Id
        })
    });
}

async function updateApplication() {
    updateApplicationRef.value.statusLoading();
    let formData = {
        Id: ApplicationModel.value.Id,
        Name: ApplicationModel.value.Name,
        Platform: ApplicationModel.value.Platform,
        OperatingSystem: ApplicationModel.value.OperatingSystem
    };

    try {
        let {data, message} = await Application.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateApplicationRef.value.statusNormal();
}


const assigneeModuleFormModal = ref(null);
const editApplicationModuleModal = ref(null);

function previewAssignModuleForm() {
    assigneeModuleFormModal.value.openModal();
}

let SelectedApplicationModule = ref({});

function previewEditApplicationModuleForm(row) {
    editApplicationModuleModal.value.openModal();
    SelectedApplicationModule.value = {
        Id: row.ApplicationModuleId,
        ApplicationId: row.ApplicationId,
        ModuleId: row.ModuleId,
        AlwaysEnabled: row.AlwaysEnabled === 'Yes',
        ApplicationVersionStart: row.ApplicationVersionStart,
        ApplicationVersionEnd: row.ApplicationVersionEnd,
        Description: row.Description,
        Title: row.Title,
        SubTitle: row.SubTitle,
    };
}


let ModuleId = ref('');
let AlwaysEnabled = ref(false);
let ApplicationVersionStart = ref('');
let ApplicationVersionEnd = ref('');
let Description = ref('');
let Title = ref('');
let SubTitle = ref('');

async function assignModuleToApplication() {
    let formData = {
        ApplicationId: ApplicationModel.value.Id,
        ModuleId: ModuleId.value,
        AlwaysEnabled: AlwaysEnabled.value,
        ApplicationVersionStart: ApplicationVersionStart.value,
        ApplicationVersionEnd: ApplicationVersionEnd.value,
        Title: Title.value,
        SubTitle: SubTitle.value,
        Description: Description.value,
    };
    assignModuleToApplicationRef.value.statusLoading();

    try {
        let {message} = await ApplicationModule.create(formData);
        assignModuleToApplicationRef.value.statusNormal();
        assigneeModuleFormModal.value.closeModal();
        updateApplicationRef.value.statusLoading();
        await getApplicationDetails();
        await getAssignableModulesToApplication();
        resetAssignModuleToApplicationForm();
        notificationStore.showNotification(message);
        updateApplicationRef.value.statusNormal();
    } catch (error) {
        setErrorsAssignForm(error.response.data.errors);
        assignModuleToApplicationRef.value.statusNormal();
    }
}

function resetAssignModuleToApplicationForm() {
    ModuleId.value = '';
    AlwaysEnabled.value = false;
    ApplicationVersionStart.value = '';
    ApplicationVersionEnd.value = '';
    Title.value = '';
    SubTitle.value = '';
    Description.value = '';
}

function resetEditApplicationModuleForm() {
    SelectedApplicationModule.value = {};
}

async function updateApplicationModuleInfo() {
    let formData = SelectedApplicationModule.value;
    assignModuleToApplicationRef.value.statusLoading();

    try {
        let {message} = await ApplicationModule.update(formData);
        assignModuleToApplicationRef.value.statusNormal();
        editApplicationModuleModal.value.closeModal();
        updateApplicationRef.value.statusLoading();
        await getApplicationDetails();
        await getAssignableModulesToApplication();
        resetEditApplicationModuleForm();
        notificationStore.showNotification(message);
        updateApplicationRef.value.statusNormal();
    } catch (error) {
        setErrorsEditForm(error.response.data.errors);
        assignModuleToApplicationRef.value.statusNormal();
    }
}

onMounted(async () => {
    updateApplicationRef.value.statusLoading();
    await getApplicationDetails();
    await getAssignableModulesToApplication();
    updateApplicationRef.value.statusNormal();
});

</script>

<template>
    <div class="content">
        <div class="row">
            <div class="col-4">
                <BaseBlock ref="updateApplicationRef" content-full title="Edit Application">
                    <template #options>
                        <router-link :to="{name:'applications'}" class="btn btn-sm btn-outline-info">
                            <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                        </router-link>
                    </template>
                    <form class="space-y-2" @submit.prevent="updateApplication">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="ApplicationModel.Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Name"
                                       placeholder="Name"
                                       required
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Platform">
                                Platform<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Platform" v-model="ApplicationModel.Platform"
                                       :class="errors.Platform ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Platform"
                                       placeholder="Platform"
                                       required
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.Platform"
                                                    :errorMessages="errors.Platform"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="OperatingSystem">
                                OperatingSystem<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="OperatingSystem" v-model="ApplicationModel.OperatingSystem"
                                       :class="errors.OperatingSystem ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="OperatingSystem"
                                       placeholder="OperatingSystem"
                                       required
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.OperatingSystem"
                                                    :errorMessages="errors.OperatingSystem"></InputErrorMessages>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
                    </form>
                </BaseBlock>
            </div>
            <div class="col-8">
                <ApplicationModulesTable :application-modules="ApplicationModules"
                                         @assignModuleToApplication="previewAssignModuleForm"
                                         @editApplicationModule="previewEditApplicationModuleForm($event)"
                />
            </div>
        </div>

        <ModalComponent id="assigneeModuleFormModal" ref="assigneeModuleFormModal">
            <template v-slot:modal-content>
                <BaseBlock ref="assignModuleToApplicationRef" :title="`Assign To ${ApplicationModel.Name}`" class="mb-0"
                           transparent>
                    <template #options>
                        <button
                            aria-label="Close"
                            class="btn-block-option"
                            data-bs-dismiss="modal"
                            type="button"
                        >
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </template>

                    <template #content>
                        <form @submit.prevent="assignModuleToApplication">
                            <div class="block-content fs-sm space-y-2 mb-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="ModuleId">
                                        Module<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="ModuleId" v-model="ModuleId" :options="AssignableModuleOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.ModuleId ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="ModuleId"
                                                @change="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.ModuleId"
                                                            :errorMessages="errorsAssignForm.ModuleId"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="AlwaysEnabled">
                                        Always Enabled<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="AlwaysEnabled" v-model="AlwaysEnabled" :options="booleanOptions"
                                                :required="true"
                                                :select-class="errorsAssignForm.AlwaysEnabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="AlwaysEnabled"
                                                @change="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.AlwaysEnabled"
                                                            :errorMessages="errorsAssignForm.AlwaysEnabled"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Title">
                                        Title
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Title" v-model="Title"
                                               :class="errorsAssignForm.Title ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Title"
                                               type="text"
                                               @keyup="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.Title"
                                                            :errorMessages="errorsAssignForm.Title"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="SubTitle">
                                        SubTitle
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="SubTitle" v-model="SubTitle"
                                               :class="errorsAssignForm.SubTitle ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="SubTitle"
                                               type="text"
                                               @keyup="resetErrorsAssignForm"/>
                                        <InputErrorMessages v-if="errorsAssignForm.SubTitle"
                                                            :errorMessages="errorsAssignForm.SubTitle"></InputErrorMessages>
                                    </div>
                                </div>
                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                                <button
                                    class="btn btn-sm btn-primary"
                                    type="submit"
                                >
                                    Save
                                </button>
                                <button
                                    class="btn btn-sm btn-alt-secondary me-1"
                                    data-bs-dismiss="modal"
                                    type="button">
                                    Close
                                </button>
                            </div>
                        </form>

                    </template>
                </BaseBlock>
            </template>
        </ModalComponent>

        <ModalComponent id="editApplicationModuleModal" ref="editApplicationModuleModal">
            <template v-slot:modal-content>
                <BaseBlock ref="editApplicationModuleRef" :title="`Edit`" class="mb-0"
                           transparent>
                    <template #options>
                        <button
                            aria-label="Close"
                            class="btn-block-option"
                            data-bs-dismiss="modal"
                            type="button"
                        >
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </template>

                    <template #content>
                        <form @submit.prevent="updateApplicationModuleInfo">
                            <div class="block-content fs-sm space-y-2 mb-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="ModuleId">
                                        Module<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="ModuleId" v-model="SelectedApplicationModule.ModuleId"
                                                :options="ApplicationModulesOptions"
                                                :required="true"
                                                :select-class="errorsEditForm.ModuleId ? `is-invalid form-select-sm` : `form-select-sm`"
                                                disabled
                                                name="ModuleId"
                                                @change="resetErrorsEditForm"/>
                                        <InputErrorMessages v-if="errorsEditForm.ModuleId"
                                                            :errorMessages="errorsEditForm.ModuleId"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="AlwaysEnabled">
                                        Always Enabled<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="AlwaysEnabled" v-model="SelectedApplicationModule.AlwaysEnabled"
                                                :options="booleanOptions"
                                                :required="true"
                                                :select-class="errorsEditForm.AlwaysEnabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="AlwaysEnabled"
                                                @change="resetErrorsEditForm"/>
                                        <InputErrorMessages v-if="errorsEditForm.AlwaysEnabled"
                                                            :errorMessages="errorsEditForm.AlwaysEnabled"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Title">
                                        Title
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Title" v-model="SelectedApplicationModule.Title"
                                               :class="errorsEditForm.Title ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Title"
                                               type="text"
                                               @keyup="resetErrorsEditForm"/>
                                        <InputErrorMessages v-if="errorsEditForm.Title"
                                                            :errorMessages="errorsEditForm.Title"></InputErrorMessages>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="SubTitle">
                                        SubTitle
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="SubTitle" v-model="SelectedApplicationModule.SubTitle"
                                               :class="errorsEditForm.SubTitle ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="SubTitle"
                                               type="text"
                                               @keyup="resetErrorsEditForm"/>
                                        <InputErrorMessages v-if="errorsEditForm.SubTitle"
                                                            :errorMessages="errorsEditForm.SubTitle"></InputErrorMessages>
                                    </div>
                                </div>
                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                                <button
                                    class="btn btn-sm btn-primary"
                                    type="submit"
                                >
                                    Save
                                </button>
                                <button
                                    class="btn btn-sm btn-alt-secondary me-1"
                                    data-bs-dismiss="modal"
                                    type="button">
                                    Close
                                </button>
                            </div>
                        </form>

                    </template>
                </BaseBlock>
            </template>
        </ModalComponent>
    </div>
</template>
