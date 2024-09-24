<script setup>

import {onMounted, ref} from "vue";
import VueSelect from 'vue-select';
import Company from "@/models/Office/Company";
import Database from "@/models/Office/Database";
import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();

let CompanyOptions = ref([]);

// Form Values
let selectedDatabases = ref([]);

let loading = ref({
    companiesDropDown: false
});

let errors = ref({});

onMounted(() => {
    getAllCompanies();
});

async function getAllCompanies() {
    selectedDatabases.value = [];

    loading.value.companiesDropDown = true;
    const {data} = await Database.getAllCompanies();
    let options = [{label: 'Select Database', value: ''}, {label: 'OFFICE--NVISION_OFFICE', value: 'NVISION_OFFICE'},];

    data.forEach((company) => {
        let option = {label: company.Name + '--' + company.DatabaseName, value: company.DatabaseName};
        options.push(option);
    });

    CompanyOptions.value = options;
    console.log(CompanyOptions.value);
    loading.value.companiesDropDown = false;
}

async function submit() {

    let formData = {
        // selectedDatabases: selectedDatabases.value,
        selectedDatabases: selectedDatabases.value.filter(value => value !== ""),
    };
    console.log(formData);

    // let {data, message} = await Database.copyDBtoDev(formData);
    // notificationStore.showNotification(message);


    try {
        let {data, message} = await Database.copyDBtoDev(formData);
        notificationStore.showNotification(message);
    } catch (err) {
        if (err.response.status === 422) {
            errors.value = err.response.data.errors;
        }
    }
}

</script>

<template>
    <form class="space-y-2" @submit.prevent="submit">
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
                            :inputId="`Companies`"
                            :loading="loading.companiesDropDown"
                            :options="CompanyOptions"
                            :reduce="company => company.value"
                            :searchable="true"
                            multiple
                            placeholder="Select Companies/Database">
                        </VueSelect>

                        <!--                        <Select id="Company" v-model="selectedDatabases" :options="CompanyOptions"-->
                        <!--                                :required="true"-->
                        <!--                                :select-class="errors.Company ? `is-invalid form-select-sm` : `form-select-sm`"-->
                        <!--                                name="Company"-->
                        <!--                        />-->
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
</template>
