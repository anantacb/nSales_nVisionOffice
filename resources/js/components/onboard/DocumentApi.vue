<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
    </h4>
    <div class="row">
        <div class="col-12">
            <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
            <table v-else-if="!isLoading && documentApis && documentApis.length > 0" class="table">
                <thead class="table-dark">
                <tr>
                    <td>Id</td>
                    <td>Name</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="documentApi in documentApis">
                    <td>
                        {{ documentApi.Id }}
                    </td>
                    <td>{{ documentApi.Name }}</td>
                </tr>
                </tbody>
            </table>
            <div class="m-6 text-center" v-else>
                <h6>No document api added yet!</h6>
                <a href="https://app.nsales.io/document-apis" target="_blank" class="btn btn-primary">Add document api</a>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import Loader from "@/components/ui/Loader/Loader.vue";
import DocumentApi from "@/models/Company/DocumentApi";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

onMounted(() => {
    getCompanyDocumentApi();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyDocumentApi();
    }
});

const isLoading = ref(false)
const documentApis = ref([])

const getCompanyDocumentApi = async () => {
    try {
        isLoading.value = true

        let {data} = await DocumentApi.list(companyStore.selectedCompany.Id);
        documentApis.value = data

        if (documentApis.value && documentApis.value.length > 0) {
            emits("complete", true)
        }

        isLoading.value = false
    } catch (err) {

        isLoading.value = false
    }
}

emits("complete", true)
</script>
