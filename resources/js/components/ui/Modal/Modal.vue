<script setup>
import {onMounted, ref} from "vue";
import {Modal} from 'bootstrap';

defineProps({
    id: {
        required: true,
        type: String
    },
    modalBodyClasses: {
        required: false,
        type: String,
        default: () => {
            return ''
        }
    }
});

const modalRef = ref(null);

let modal = ref(null);

onMounted(() => {
    modal.value = new Modal(modalRef.value);
});

function openModal() {
    modal.value.show();
}

function closeModal() {
    modal.value.hide();
}

defineExpose({
    openModal,
    closeModal
});

</script>
<template>
    <div
        :id="id"
        ref="modalRef"
        :aria-labelledby="`${id}-label`"
        aria-hidden="true"
        class="modal"
        role="dialog"
        tabindex="-1"
    >
        <div :class="modalBodyClasses"
             class="modal-dialog"
             role="document">
            <div class="modal-content">
                <slot name="modal-content"></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
