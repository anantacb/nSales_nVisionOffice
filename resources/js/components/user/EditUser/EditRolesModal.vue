<script setup>
import {ref} from "vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import VueSelect from "vue-select";
import Role from "@/models/Office/Role";
import User from "@/models/Office/User";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

const emit = defineEmits(['updated']);

const modal = ref(null);
const editRolesRef = ref(null);

const CompanyUserId = ref(null);
const CompanyName = ref('');
const RoleIds = ref([]);
const RoleOptions = ref([]);
const loadingOptions = ref(false);

async function openWith(row) {
    resetErrors();
    CompanyUserId.value = row.CompanyUserId;
    CompanyName.value = row.CompanyName;
    RoleIds.value = (row.RoleObjects || []).map(r => r.Id);
    RoleOptions.value = [];
    modal.value.openModal();

    loadingOptions.value = true;
    try {
        const {data} = await Role.getRolesByCompany(row.CompanyId, true);
        RoleOptions.value = data;
    } finally {
        loadingOptions.value = false;
    }
}

function handleDeselected(option) {
    // Prevent removing the last role — re-add it.
    if (RoleIds.value.length === 0) {
        RoleIds.value = [option.Id];
    }
    resetErrors();
}

async function save() {
    if (RoleIds.value.length === 0) return;

    editRolesRef.value.statusLoading();

    try {
        const {message} = await User.updateCompanyUserRoles({
            CompanyUserId: CompanyUserId.value,
            RoleIds: RoleIds.value
        });
        editRolesRef.value.statusNormal();
        modal.value.closeModal();
        notificationStore.showNotification(message);
        emit('updated');
    } catch (error) {
        editRolesRef.value.statusNormal();
        if (error.response && error.response.data && error.response.data.errors) {
            setErrors(error.response.data.errors);
        }
    }
}

defineExpose({openWith});
</script>

<template>
    <ModalComponent id="editRolesFormModal" ref="modal">
        <template v-slot:modal-content>
            <BaseBlock ref="editRolesRef" class="mb-0" title="Edit Roles" transparent>
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
                    <form @submit.prevent="save">
                        <div class="block-content fs-sm space-y-2 mb-2">
                            <div class="row" v-if="CompanyName">
                                <label class="col-sm-4 col-form-label col-form-label-sm">
                                    Company
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control-plaintext form-control-sm">
                                        {{ CompanyName }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-4 col-form-label col-form-label-sm" for="EditRoles">
                                    Roles<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <VueSelect
                                        v-model="RoleIds"
                                        :class="{'is-invalid': errors.RoleIds}"
                                        :clearable="false"
                                        :get-option-label="role => role.Name"
                                        :inputId="`EditRoles`"
                                        :loading="loadingOptions"
                                        :options="RoleOptions"
                                        :reduce="role => role.Id"
                                        :searchable="true"
                                        multiple
                                        placeholder="Select Roles"
                                        @option:selected="resetErrors"
                                        @option:deselected="handleDeselected"
                                    >
                                    </VueSelect>
                                    <InputErrorMessages v-if="errors.RoleIds"
                                                        :errorMessages="errors.RoleIds"></InputErrorMessages>
                                    <small class="text-muted">At least one role is required.</small>
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button
                                :disabled="RoleIds.length === 0"
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
</template>

<style scoped>
</style>
