<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
    </h4>
    <div class="row position-relative">
        <div class="col-12">
            <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
            <table v-else-if="!isLoading && branches && Object.keys(branches).length > 0" class="table">
                <thead class="table-dark">
                <tr>
                    <td>Name</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="branch in branches">
                    <td>
                        {{ branch.name }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="m-6 text-center" v-else>
                <h6>No branch added yet!</h6>
                <button class="btn btn-outline-success" @click.prevent="createCompanyBranches">Create branches</button>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import _ from "lodash";
import Theme from "@/models/Company/Theme";
import Loader from "@/components/ui/Loader/Loader.vue";
import Git from "@/models/Company/Git";
import Button from "@/components/ui/Button.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

onMounted(() => {
    getCompanyBranches();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyBranches();
    }
});

const isLoading = ref(false)
const branches = ref([])

const getCompanyBranches = async () => {
    try {
        isLoading.value = true

        let {data} = await Git.getCompanyBranches(companyStore.selectedCompany.Id);
        branches.value = data

        if (branches.value && Object.keys(branches.value).length > 0) {
            emits("complete", true)
        }

        isLoading.value = false
    } catch (err) {
        notificationStore.showNotification(err.response.data.message, "danger")
        isLoading.value = false
    }
}

const createCompanyBranches = async () => {
    try {
        isLoading.value = true

        await Git.createCompanyBranches(companyStore.selectedCompany.Id);

        isLoading.value = false

        getCompanyBranches();
    } catch (err) {
        notificationStore.showNotification(err.response.data.message, "danger")
        isLoading.value = false
    }
}
</script>
