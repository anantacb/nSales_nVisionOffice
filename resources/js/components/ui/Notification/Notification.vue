<script setup>
import {computed, onMounted} from "vue";

const props = defineProps({
    index: {
        type: Number,
        required: true
    },

    timer: {
        type: Number,
        default: () => 5000,
        required: false
    },

    message: {
        type: String,
        required: true
    },

    type: {
        type: String,
        required: true,
        validator(value) {
            return ['success', 'error'].includes(value);
        }
    }
});

onMounted(() => {
    let notificationElement = new window.bootstrap.Toast(
        window.document.getElementById(`notification-element-${props.index}`), {
            autohide: true,
            animation: true,
            delay: props.timer
        }
    );
    notificationElement.show();
});

let iconClasses = computed(() => {
    return props.type === 'success' ? 'text-success far fa-circle-check' : 'text-danger far fa-circle-xmark'
})

let title = computed(() => {
    return props.type === 'success' ? 'Success' : 'Error';
})

</script>

<template>

    <!-- Toast Example 1 -->
    <div
        :id="`notification-element-${index}`"
        aria-atomic="true"
        aria-live="assertive"
        class="toast fade hide"
        role="alert"
    >
        <div class="toast-header">
            <i :class="iconClasses" class="me-2"></i>
            <strong :class="type===`success`? `text-success` : `text-danger`" class="me-auto">{{ title }}</strong>
            <small class="text-muted">Just Now</small>
            <button
                aria-label="Close"
                class="btn-close"
                data-bs-dismiss="toast"
                type="button"
            ></button>
        </div>
        <div class="toast-body">
            {{ message }}
        </div>
    </div>
    <!-- END Toast Example 1 -->

</template>

<style scoped>

</style>
