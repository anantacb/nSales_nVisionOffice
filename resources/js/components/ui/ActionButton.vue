<script setup>

import router from "@/router";
import {ref} from "vue";

const props = defineProps({
    actionType: {
        type: String,
        required: true,
        validator(value) {
            // The value must match one of these actions
            return ['details', 'edit', 'delete'].includes(value);
        }
    },
    content: {
        type: String,
        required: false,
    },
    // routeTo: {name: 'string|RouteName', params: object|{id: id}}
    routeTo: {
        type: Object,
        required: false,
    }

});

const emit = defineEmits(['delete']);

let classObj = ref({
    'details': {
        'btn': 'btn-alt-info',
        'icon': 'fa fa-file-invoice',
    },
    'edit': {
        'btn': 'btn-alt-warning',
        'icon': 'fa fa-pen-alt',
    },
    'delete': {
        'btn': 'btn-alt-danger',
        'icon': 'fa fa-trash-alt',
    },
});

let btnContent = ref(props.content ?? props.actionType.charAt(0).toUpperCase() + props.actionType.slice(1));

function btnAction() {
    if (props.actionType === 'delete') {
        emit('delete');
    } else {
        router.push(props.routeTo);
    }
}

</script>

<template>

    <!-- For Button -->
    <PopOverButton
        :btnClass="classObj[props.actionType]['btn']"
        :content="btnContent"
        :iconClass="classObj[props.actionType]['icon']"
        class="btn rounded-pill me-1"
        @click="btnAction">
        >
    </PopOverButton>

</template>

<style scoped>

</style>
