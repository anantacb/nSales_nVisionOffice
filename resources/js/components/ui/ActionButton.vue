<script setup>

const props = defineProps({
    actionType: {
        type: String,
        required: true,
        validator(value) {
            // The value must match one of these actions
            return ['details', 'edit', 'delete'].includes(value);
        }
    },
    // routeTo: {name: 'string|RouteName', params: object|{id: id}}
    routeTo: {
        type: Object,
        required: false,
    }

});

const emit = defineEmits(['delete']);

</script>

<template>

    <!-- For Details, Edit Button -->
    <router-link
        v-if="['details','edit'].includes(props.actionType)"
        :class="props.actionType === 'edit' ? 'btn-alt-warning' : 'btn-alt-info'"
        :to="props.routeTo"
        class="btn rounded-pill me-1"
    >
        <i :class="props.actionType === 'edit' ? 'fa-pen-alt' : 'fa-file-invoice'" class="fa"></i>
    </router-link>

    <!-- For Delete Button -->
    <button
        v-if="props.actionType === 'delete'"
        class="btn rounded-pill btn-alt-danger me-1"
        type="button"
        @click="$emit('delete')"
    >
        <i class="fa fa-trash-alt"></i>
    </button>

</template>

<style scoped>

</style>
