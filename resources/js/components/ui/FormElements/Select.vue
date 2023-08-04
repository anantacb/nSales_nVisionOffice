<script setup>

const props = defineProps({
    modelValue: {},
    options: {
        type: Array,
        required: false,
        default() {
            return [
                {
                    label: "Please Select",
                    value: ""
                }
            ]
        }
    },
    id: {
        type: String,
        required: false
    },
    name: {
        type: String,
        required: false
    },
    required: {
        type: Boolean,
        required: false,
        default() {
            return false;
        }
    },
    disabled: {
        type: Boolean,
        required: false,
        default() {
            return false;
        }
    },
    selectClass: {
        type: String,
        required: false
    },
    optionClass: {
        type: String,
        required: false
    }
});

function change(value) {
    emit('update:modelValue', value);
    emit('change', value);
}

const emit = defineEmits(['update:modelValue', 'change']);

</script>

<template>
    <select :id="id" :class="selectClass" :disabled="disabled" :name="name" :required="required"
            class="form-select" @change="change($event.target.value)">
        <option v-for="option in options" :class="optionClass" :selected="modelValue === option.value"
                :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<style scoped>

</style>
