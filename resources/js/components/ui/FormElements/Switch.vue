<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
        default: false
    },
    id: {
        type: String,
        required: true
    },
    name: {
        type: String,
        required: false
    },
    label: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

function change(value) {
    emit('update:modelValue', value);
    emit('change', value);
}
</script>

<template>
    <div class="form-check form-switch">
        <input :id="id"
               :checked="modelValue"
               :name="name"
               class="form-check-input"
               type="checkbox"
               @change="change($event.target.checked)"
        />
        <label :for="id" class="form-check-label">{{ label }}</label>
    </div>
</template>

<style scoped>

</style>
