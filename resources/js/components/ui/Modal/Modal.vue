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

let areaHidden = ref(true);

onMounted(() => {
    modal.value = new Modal(modalRef.value);
});

function openModal() {
    areaHidden.value = false;
    modal.value.show();
}

function closeModal() {
    areaHidden.value = true;
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
        :aria-hidden="areaHidden"
        :aria-labelledby="`${id}-label`"
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
