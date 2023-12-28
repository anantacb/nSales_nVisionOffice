<script setup>
import router from "@/router";

const props = defineProps({
    routerName: {
        type: String,
        default: ""
    }
});

function goBack() {
    return props.routerName ? router.push({name: props.routerName}) : router.go(-1);
}

</script>

<template>
    <button class="btn btn-sm btn-outline-info" @click="goBack">
        <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
    </button>
</template>

<style scoped>

</style>
