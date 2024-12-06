<template>
    <h4 class="d-flex justify-content-between">
        CustomLogicCheck
    </h4>

</template>
<script setup>
import {computed, defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import Module from "@/models/Office/Module";
import _ from "lodash";
import Button from "@/components/ui/Button.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)

emits("complete", true)
</script>
