<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
        <button :disabled="isLoading" class="btn btn-outline-success btn-sm" @click.prevent="startDeployment">
            Update
        </button>
    </h4>
    <div class="form-wrapper space-y-4">
        <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
        <div class="row">
            <div class="col-lg-12 space-y-2">
                <div class="row">
                    <label class="col-sm-3 col-form-label col-form-label-sm" for="Language">
                        Deploy On Production Server:
                    </label>
                    <div class="col-sm-9">
                        <input id="prodModelId" v-model="prodModel" class="form-check-input"
                               type="checkbox">
                    </div>
                </div>
            </div>

            <div class="col-lg-12 space-y-2">
                <div class="row">
                    <label class="col-sm-3 col-form-label col-form-label-sm" for="Language">
                        Deploy On Development Server:
                    </label>
                    <div class="col-sm-9">
                        <input id="devModelId" v-model="devModel" class="form-check-input"
                               type="checkbox">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import Loader from "@/components/ui/Loader/Loader.vue";
import Deploy from "@/models/Company/Deploy";
import {useNotificationStore} from "@/stores/notificationStore";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();
const companyDeploymentStatus = ref([])
const isLoading = ref(false);
const prodModel = ref(false);
const devModel = ref(false);

async function getCompanyDeploymentStatus() {
    try {
        isLoading.value = true
        const {data} = await Deploy.getCompanyDeploymentStatus(companyStore.selectedCompany.Id);
        // console.log(data);
        companyDeploymentStatus.value = data;
        prodModel.value = !!(companyDeploymentStatus.value.prod.uuid && companyDeploymentStatus.value.prod.hosts[0].active);
        devModel.value = !!(companyDeploymentStatus.value.dev.uuid && companyDeploymentStatus.value.dev.hosts[0].active);

        if (prodModel.value && devModel.value) {
            emits("complete", true);
        }

        isLoading.value = false;
    } catch (err) {
        isLoading.value = false;
    }
}

const startDeployment = async () => {
    try {
        isLoading.value = true;
        let formData = {
            prod: prodModel.value,
            dev: devModel.value,
        };

        const {data, message} = await Deploy.startCompanyDeployment(companyStore.selectedCompany.Id, formData);

        notificationStore.showNotification(message);
        isLoading.value = false;
        await getCompanyDeploymentStatus();
    } catch (err) {
        console.log(err);
        isLoading.value = false;
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
