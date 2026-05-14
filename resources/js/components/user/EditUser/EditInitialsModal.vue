<script setup>
import {ref} from "vue";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import User from "@/models/Office/User";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";

const notificationStore = useNotificationStore();
const {errors, setErrors, resetErrors} = useFormErrors();

const emit = defineEmits(['updated']);

const modal = ref(null);
const editInitialsRef = ref(null);

const CompanyUserId = ref(null);
const CompanyId = ref(null);
const CompanyName = ref('');
const Initials = ref('');

function openWith(row) {
    resetErrors();
    CompanyUserId.value = row.CompanyUserId;
    CompanyId.value = row.CompanyId;
    CompanyName.value = row.CompanyName;
    Initials.value = row.Initials || '';
    modal.value.openModal();
}

async function save() {
    if (!Initials.value) return;

    editInitialsRef.value.statusLoading();

    try {
        const {message} = await User.updateCompanyUserInitials({
            CompanyUserId: CompanyUserId.value,
            CompanyId: CompanyId.value,
            Initials: Initials.value
        });
        editInitialsRef.value.statusNormal();
        modal.value.closeModal();
        notificationStore.showNotification(message);
        emit('updated');
    } catch (error) {
        editInitialsRef.value.statusNormal();
        if (error.response && error.response.data && error.response.data.errors) {
            setErrors(error.response.data.errors);
        }
    }
}

defineExpose({openWith});
</script>

<template>
    <ModalComponent id="editInitialsFormModal" ref="modal">
        <template v-slot:modal-content>
            <BaseBlock ref="editInitialsRef" class="mb-0" title="Edit Initials" transparent>
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
                                <label class="col-sm-4 col-form-label col-form-label-sm" for="EditInitials">
                                    Initials<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <input id="EditInitials" v-model="Initials"
                                           :class="errors.Initials ? `is-invalid form-control-sm` : `form-control-sm`"
                                           autocomplete="off" class="form-control"
                                           name="Initials"
                                           required
                                           type="text"
                                           @keyup="resetErrors"/>
                                    <InputErrorMessages v-if="errors.Initials"
                                                        :errorMessages="errors.Initials"></InputErrorMessages>
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button
                                :disabled="!Initials"
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
