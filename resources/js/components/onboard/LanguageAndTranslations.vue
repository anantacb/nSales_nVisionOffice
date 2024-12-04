<template>
    LanguageAndTranslations
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();
</script>
