<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
    </h4>
    <div class="row">
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" :checked="step.IsCompleted" @change="markAsComplete" id="defaultCheck1">
                <label class="form-check-label" for="defaultCheck1">
                    Do you want to deploy?
                </label>
            </div>
        </div>
    </div>
</template>
<script setup>
import { defineProps, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)

if(props.step.IsCompleted) {
    emits("complete", true)
}
const markAsComplete = (event) => {
    if(event.target.checked) {
        emits("complete", true)
    }
}
</script>
