<template>
    <div class="position-relative max-h-80vh h-100 overflow-auto">
        <h4 class="d-flex justify-content-between">
            Languages
        </h4>
        <div class="row">
            <div class="col-12">
                <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
                <table v-else-if="!isLoading && languages && languages.length > 0" class="table">
                    <thead class="table-dark">
                    <tr>
                        <td>Name</td>
                        <td>Code</td>
                        <td>Locale</td>
                        <td>Default</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="language in languages">
                        <td>{{ language.Name }}</td>
                        <td>{{ language.Code }}</td>
                        <td>{{ language.Locale }}</td>
                        <td>{{ language.IsDefault }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="m-8 text-center" v-else>
                    <h6>No language added yet!</h6>
                    <RouterLink :to="{ name: 'company-languages' }" class="btn btn-primary">Add Language</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import Loader from "@/components/ui/Loader/Loader.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

onMounted(() => {
    getCompanyLanguages();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyLanguages();
    }
});

const isLoading = ref(true)
const languages = ref([])

async function getCompanyLanguages() {
    try {
        isLoading.value = true

        let {data, pagination} = await CompanyLanguage.getCompanyLanguages(companyStore.selectedCompany.Id, {
            pagination: {"page_no": 1, "per_page": 20}
        });
        languages.value = data

        if(languages.value && languages.value.length > 0) {
            emits("complete", true)
        }

        isLoading.value = false
    } catch (err) {
        isLoading.value = false
    }
}
</script>
