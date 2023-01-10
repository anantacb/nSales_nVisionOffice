<template>
    <div v-for="errorMessage in errorMessages" class="invalid-feedback animated fadeIn">
        {{ errorMessage }}
    </div>
</template>

<script setup>
defineProps({
    errorMessages: {
        type: Array,
        required: false,
        default: () => []
    }
})
</script>

<style scoped>

</style>
