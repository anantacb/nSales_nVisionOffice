<script setup>
import {onMounted, ref} from "vue";
import VueSelect from 'vue-select';
import Database from "@/models/Office/Database";
import {useNotificationStore} from "@/stores/notificationStore";
import _ from "lodash";

const notificationStore = useNotificationStore();
const copyDatabaseRef = ref(null);
let CompanyOptions = ref([]);
let selectedDatabases = ref([]);

let errors = ref({});
let loading = ref({
    companiesDropDown: false
});

onMounted(() => {
    getAllCompanies();
});

async function getAllCompanies() {
    selectedDatabases.value = [];

    loading.value.companiesDropDown = true;
    const {data} = await Database.getAllCompanies();
    let options = [{label: 'Select Database', value: ''}, {label: 'Office (NVISION_OFFICE)', value: 'NVISION_OFFICE'},];

    data.forEach((company) => {
        let option = {label: `${company.Name} (${company.DatabaseName})`, value: company.DatabaseName};
        options.push(option);
    });

    CompanyOptions.value = options;
    loading.value.companiesDropDown = false;
}

async function copyDatabaseToDevServer() {
    if (!selectedDatabases.value || _.isEmpty(selectedDatabases.value)) {
        errors.value.selectedDatabases = ['Please select at least one company/database.'];
        return;
    }

    copyDatabaseRef.value.statusLoading();
    let formData = {
        selectedDatabases: selectedDatabases.value.filter(value => value !== ""),
    };

    try {
        let {data, message} = await Database.copyDBtoDev(formData);
        notificationStore.showNotification(message);
        selectedDatabases.value = [];
        copyDatabaseRef.value.statusNormal();

    } catch (err) {
        if (err.response.status === 422) {
            errors.value = err.response.data.errors;
        }
        copyDatabaseRef.value.statusNormal();
    }
}

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="copyDatabaseRef" content-full title="Copy DB To Dev Server">
            <template #options>
                <router-link :to="{name:'tables'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-2" @submit.prevent="copyDatabaseToDevServer">
                <div class="row space-y-12">
                    <div class="col-lg-6 space-y-2">

                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Companies">
                                Companies/Database
                            </label>
                            <div class="col-sm-8">
                                <VueSelect
                                    v-model="selectedDatabases"
                                    :class="errors.selectedDatabases ? `is-invalid` : ``"
                                    :clearable="true"
                                    :get-option-label="company => company.label"
                                    :input-required="true"
                                    :inputId="`Companies`"
                                    :loading="loading.companiesDropDown"
                                    :options="CompanyOptions"
                                    :reduce="company => company.value"
                                    :searchable="true"
                                    multiple
                                    placeholder="Select Companies/Database">

                                    <template #search="{attributes, events}">
                                        <input
                                            :required="!_.isEmpty(selectedDatabases.value)"
                                            class="vs__search"
                                            v-bind="attributes"
                                            v-on="events"
                                        />
                                    </template>

                                </VueSelect>

                                <InputErrorMessages v-if="errors.selectedDatabases"
                                                    :errorMessages="errors.selectedDatabases"></InputErrorMessages>
                            </div>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button class="btn btn-outline-primary" type="submit">Copy DB</button>
                    </div>

                </div>
            </form>
        </BaseBlock>

    </div>
    <!-- END Page Content -->
</template>
