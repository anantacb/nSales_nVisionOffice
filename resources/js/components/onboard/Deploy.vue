<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
    </h4>
    <div class="row">
        <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
        <div class="col-12">
            <div class="form-check">
                <input id="defaultCheck1" :checked="step.IsCompleted" class="form-check-input" type="checkbox"
                       value="1" @change="markAsComplete">
                <label class="form-check-label" for="defaultCheck1">
                    Do you want to deploy?
                </label>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import WebShopPage from "@/models/Company/WebShopPage";
import Loader from "@/components/ui/Loader/Loader.vue";
import Deploy from "@/models/Company/Deploy";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)

if (props.step.IsCompleted) {
    emits("complete", true)
}
const markAsComplete = (event) => {
    if (event.target.checked) {
        emits("complete", true)
    }
}

const pages = ref([])

async function getCompanyDeploymentStatus() {
    try {
        isLoading.value = true

        // const platform = props.step.payload && props.step.payload.platform ? props.step.payload.platform : "web";
        const {data} = await Deploy.getCompanyDeploymentStatus(companyStore.selectedCompany.Id);
        console.log(data);
        pages.value = data

        isLoading.value = false
    } catch (err) {
        isLoading.value = false
    }
}

onMounted(() => {
    getCompanyDeploymentStatus();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyDeploymentStatus();
    }
});

</script>
