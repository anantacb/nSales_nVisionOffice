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
    selectClass: {
        type: String,
        required: false
    },
    optionClass: {
        type: String,
        required: false
    }
});

defineEmits(['update:modelValue']);

</script>

<template>
    <select :id="id" :class="selectClass" :name="name" :required="required" class="form-select"
            @change="$emit('update:modelValue', $event.target.value)">
        <option v-for="option in options" :class="optionClass" :selected="modelValue === option.value"
                :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<style scoped>

</style>
