<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
    </h4>
    <div class="row">
        <div class="col-12">
            <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
            <table v-else-if="!isLoading && theme && theme.length > 0" class="table">
                <thead class="table-dark">
                <tr>
                    <td>Name</td>
                    <td>Disabled</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="language in theme">
                    <td>
                        <img class="img-fluid me-2" :src="language.theme.PreviewImageUrl.split(',')[0]" width="50">
                        {{ language.theme.Name }}
                    </td>
                    <td>{{ language.Disabled }}</td>
                </tr>
                </tbody>
            </table>
            <div class="m-6 text-center" v-else>
                <h6>No theme added yet!</h6>
                <a href="https://app.nsales.io/e-commerce/themes" target="_blank" class="btn btn-primary">Add Theme</a>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import Theme from "@/models/Company/Theme";
import Loader from "@/components/ui/Loader/Loader.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

onMounted(() => {
    getCompanyTheme();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyTheme();
    }
});

const isLoading = ref(false)
const theme = ref([])

const getCompanyTheme = async () => {
    try {
        isLoading.value = true

        let {data} = await Theme.details(companyStore.selectedCompany.Id);
        theme.value = data

        if (theme.value && theme.value.length > 0) {
            emits("complete", true)
        }

        isLoading.value = false
    } catch (err) {

        isLoading.value = false
    }
}
</script>
