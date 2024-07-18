<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import ModulePackage from "@/models/Office/ModulePackage";
import ModulePackageModulesTable from "@/views/module-package/EditModulePackage/ModulePackageModulesTable.vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import Module from "@/models/Office/Module";
import ModulePackageModule from "@/models/Office/ModulePackageModule";
import TableHelper from "@/models/TableHelper";

const route = useRoute();
const notificationStore = useNotificationStore();

let {errors, setErrors, resetErrors} = useFormErrors();
let {errors: errorsAssignForm, setErrors: setErrorsAssignForm, resetErrors: resetErrorsAssignForm} = useFormErrors();
let {errors: errorsEditForm, setErrors: setErrorsEditForm, resetErrors: resetErrorsEditForm} = useFormErrors();

const updateModulePackageRef = ref(null);
const assignModuleToModulePackageRef = ref(null);
const ModulePackageModel = ref({});
const ModulePackageModules = ref([]);

let TypeOptions = ref([]);

async function getTypes() {
    let {data: TypeData} = await TableHelper.getEnumValues('Office', `ModulePackage`, 'Type');
    TypeOptions.value = TypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

async function getModulePackageDetails() {
    let {data} = await ModulePackage.details(route.params.id);
    ModulePackageModel.value = data;
    ModulePackageModules.value = data.modules;
}


let AssignableModuleOptions = ref([]);

async function getAssignableModulesToModulePackage() {
    let {data, message} = await Module.getAssignableModulesByModulePackage(route.params.id);
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
        });
    });
}

async function updateModulePackage() {
    updateModulePackageRef.value.statusLoading();
    let formData = {
        Id: ModulePackageModel.value.Id,
        Name: ModulePackageModel.value.Name,
        Type: ModulePackageModel.value.Type
    };

    try {
        let {data, message} = await ModulePackage.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateModulePackageRef.value.statusNormal();
}


const assigneeModuleFormModal = ref(null);
const editModulePackageModuleModal = ref(null);

function previewAssignModuleForm() {
    assigneeModuleFormModal.value.openModal();
}

let SelectedModulePackageModule = ref({});

let ModuleId = ref('');

async function assignModuleToModulePackage() {
    let formData = {
        ModulePackageId: ModulePackageModel.value.Id,
        ModuleId: ModuleId.value
    };
    assignModuleToModulePackageRef.value.statusLoading();

    try {
        let {message} = await ModulePackageModule.create(formData);
        assignModuleToModulePackageRef.value.statusNormal();
        assigneeModuleFormModal.value.closeModal();
        updateModulePackageRef.value.statusLoading();
        await getModulePackageDetails();
        await getAssignableModulesToModulePackage();
        resetAssignModuleToModulePackageForm();
        notificationStore.showNotification(message);
        updateModulePackageRef.value.statusNormal();
    } catch (error) {
        setErrorsAssignForm(error.response.data.errors);
        assignModuleToModulePackageRef.value.statusNormal();
    }
}

function resetAssignModuleToModulePackageForm() {
    ModuleId.value = '';
}

function resetEditModulePackageModuleForm() {
    SelectedModulePackageModule.value = {};
}

async function updateModulePackageModuleInfo() {
    let formData = SelectedModulePackageModule.value;
    assignModuleToModulePackageRef.value.statusLoading();

    try {
        let {message} = await ModulePackageModule.update(formData);
        assignModuleToModulePackageRef.value.statusNormal();
        editModulePackageModuleModal.value.closeModal();
        updateModulePackageRef.value.statusLoading();
        await getModulePackageDetails();
        await getAssignableModulesToModulePackage();
        resetEditModulePackageModuleForm();
        notificationStore.showNotification(message);
        updateModulePackageRef.value.statusNormal();
    } catch (error) {
        setErrorsEditForm(error.response.data.errors);
        assignModuleToModulePackageRef.value.statusNormal();
    }
}

onMounted(async () => {
    updateModulePackageRef.value.statusLoading();
    await getTypes();
    await getModulePackageDetails();
    await getAssignableModulesToModulePackage();
    updateModulePackageRef.value.statusNormal();
});

</script>

<template>
    <div class="content">
        <div class="row">
            <div class="col-4">
                <BaseBlock ref="updateModulePackageRef" content-full title="Edit ModulePackage">
                    <template #options>
                        <router-link :to="{name:'module-packages'}" class="btn btn-sm btn-outline-info">
                            <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                        </router-link>
                    </template>
                    <form class="space-y-2" @submit.prevent="updateModulePackage">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="ModulePackageModel.Name"
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="ModulePackageModel.Type" :options="TypeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Type"
                                />
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
                    </form>
                </BaseBlock>
            </div>
            <div class="col-8">
                <ModulePackageModulesTable :module-package-modules="ModulePackageModules"
                                           @assignModuleToModulePackage="previewAssignModuleForm"
                />
            </div>
        </div>

        <ModalComponent id="assigneeModuleFormModal" ref="assigneeModuleFormModal">
            <template v-slot:modal-content>
                <BaseBlock ref="assignModuleToModulePackageRef"
                           :title="`Assign To ${ModulePackageModel.Name}`"
                           class="mb-0" transparent>
                    <template #options>
                        <button
                            aria-label="Close"
                            class="btn-block-option"
                            data-bs-dismiss="modal"
                            type="button">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </template>
                    <template #content>
                        <form @submit.prevent="assignModuleToModulePackage">
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
