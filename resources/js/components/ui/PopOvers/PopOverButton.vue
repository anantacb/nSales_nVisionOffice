<script setup>
import {onMounted, onUnmounted, ref} from "vue";

const props = defineProps({
    title: {
        type: String,
        default: () => {
            return ''
        }
    },
    content: {
        type: String,
        required: true
    },
    placement: {
        type: String,
        default: () => {
            return 'top'
        },
        validator(value) {
            return ['top', 'right', 'bottom', 'left'].includes(value);
        }
    },
    btnText: {
        type: String,
        default: () => {
            return "";
        }
    },
    btnClass: {
        type: String,
        default: () => {
            return "";
        }
    },
    iconClass: {
        type: String,
        default: () => {
            return "";
        }
    },
    trigger: {
        type: String,
        required: false,
        default: () => {
            return 'hover focus';
        },
        validator(value) {
            return ['click', 'hover focus'].includes(value);
        }
    }
});
//const emit = defineEmits(['click']);

const popOverElement = ref('');
let popOver = ref(null);

onMounted(() => {
    popOver = new window.bootstrap.Popover(popOverElement.value, {
        container: popOverElement.value.dataset.bsContainer || "#page-container",
        animation:
            !!(popOverElement.value.dataset.bsAnimation &&
                popOverElement.value.dataset.bsAnimation.toLowerCase() === "true"),
        //trigger: props.trigger,
    });
});

onUnmounted(() => {
    popOver.dispose();
});
</script>
<template>
    <button ref="popOverElement" :class="btnClass"
            :data-bs-content="content"
            :data-bs-placement="placement"
            :data-bs-trigger="trigger"
            :title="title"
            data-bs-animation="true"
            data-bs-toggle="popover"
            type="button"
            @click.prevent="$emit('click')"
    >
        <i v-if="iconClass" :class="iconClass"></i>
        {{ btnText }}
    </button>
</template>

<style scoped>

</style>
