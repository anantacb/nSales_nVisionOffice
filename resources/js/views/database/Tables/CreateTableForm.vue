<script setup>

import {onMounted, ref} from "vue";

import Select from "@/components/ui/FormElements/Select.vue";
import VueSelect from 'vue-select';
import Module from "@/models/Module";
import Company from "@/models/Company";

const typeOptions = [
    {
        label: "Server",
        value: "Server",
    },
    {
        label: "Client",
        value: "Client",
    },
    {
        label: "Both",
        value: "Both",
    },
];

const databaseOptions = [
    {
        label: "Company",
        value: "Company",
    },
    {
        label: "Office",
        value: "Office",
    },
    {
        label: "Both",
        value: "Both",
    },
];

const clientSyncOptions = [
    {
        label: "",
        value: "",
    },
    {
        label: "Download",
        value: "Download",
    },
    {
        label: "Both",
        value: "Both",
    },
];

const booleanOptions = [
    {
        label: "No",
        value: "0"
    },
    {
        label: "Yes",
        value: "1"
    }
]

let modules = ref();
let companies = ref([]);

// Form Values
let name = ref("");
let type = ref("Server");
let database = ref("Company");
let module = ref("");
let selectedCompanies = ref([]);
let disabled = ref("0");

let clientSync = ref("");
let autoNumbering = ref("0");
let enableTruncate = ref("1");

let sqlTruncate = ref("");
let sqlSeed = ref("");
let note = ref("");


let loading = ref({
    moduleDropDown: false,
    companiesDropDown: false
});

onMounted(() => {
    getAllModules();
    getAllCompanies();
});

function getAllModules() {
    loading.value.moduleDropDown = true;
    Module.getAllModules()
        .then((data) => {
            modules.value = data.data;
            loading.value.moduleDropDown = true;
        })
        .catch((err) => {

        });
}

function getAllCompanies() {
    loading.value.companiesDropDown = true;
    Company.getAllCompanies()
        .then((data) => {
            companies.value = data.data;
            loading.value.companiesDropDown = true;
        })
        .catch((err) => {
        });
}

function submit() {

}

</script>

<template>
    <form class="space-y-4" @sumbit.prevent>
        <div class="row">
            <div class="col-lg-6 space-y-3">
                <!-- Form Horizontal - Default Style -->

                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Name">
                        Name
                    </label>
                    <div class="col-sm-8">
                        <input id="Name" v-model="name" class="form-control" name="Name" placeholder="Name" required
                               type="text"/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Type">
                        Type
                    </label>
                    <div class="col-sm-8">
                        <Select id="Type" v-model="type" :options="typeOptions" :required="true" name="Type"/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Database">
                        Database
                    </label>
                    <div class="col-sm-8">
                        <Select id="Database" v-model="database" :options="databaseOptions" :required="true"
                                name="Database"/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Module">
                        Module
                    </label>
                    <div class="col-sm-8">
                        <VueSelect
                            v-model="module"
                            :clearable="false"
                            :get-option-label="module => module.Name"
                            :inputId="`Module`"
                            :inputName="`Module`"
                            :loading="loading.moduleDropDown"
                            :options="modules"
                            :reduce="module => module.Id"
                            :searchable="true"
                            placeholder="Choose a value.."
                            required></VueSelect>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Companies">
                        Companies
                    </label>
                    <div class="col-sm-8">
                        <VueSelect
                            v-model="selectedCompanies"
                            :clearable="true"
                            :get-option-label="company => company.Name"
                            :inputId="`Companies`"
                            :loading="loading.companiesDropDown"
                            :options="companies"
                            :reduce="company => company.Id"
                            :searchable="true"
                            multiple
                            placeholder="Choose a value.."
                            required></VueSelect>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Disabled">
                        Disabled
                    </label>
                    <div class="col-sm-8">
                        <Select id="Disabled" v-model="disabled" :options="booleanOptions" :required="true"
                                name="Disabled"/>
                    </div>
                </div>
                <!-- END Form Horizontal - Default Style -->
            </div>
            <div class="col-lg-6 space-y-3">
                <!-- Form Horizontal - Default Style -->

                <div class="row">
                    <label class="col-sm-4 col-form-label" for="ClientSync">
                        Client Sync
                    </label>
                    <div class="col-sm-8">
                        <Select id="ClientSync" v-model="clientSync" :options="clientSyncOptions" :required="false"
                                name="ClientSync"/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="AutoNumbering">
                        Auto Numbering
                    </label>
                    <div class="col-sm-8">
                        <Select id="AutoNumbering" v-model="autoNumbering" :options="booleanOptions"
                                :required="true" name="AutoNumbering"/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="example-hf-password">
                        Enable Truncate:
                    </label>
                    <div class="col-sm-8">
                        <Select v-model="enableTruncate" :options="booleanOptions" :required="true"/>
                    </div>
                </div>

                <!-- END Form Horizontal - Default Style -->
            </div>

            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="">
                        Sql Truncate
                    </label>
                    <div class="col-sm-8">
                        <textarea v-model="sqlTruncate" class="form-control" placeholder=""></textarea>
                    </div>
                </div>

            </div>
            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="SqlSeed">
                        Sql Seed
                    </label>
                    <div class="col-sm-8">
                        <textarea id="SqlSeed" v-model="sqlSeed" class="form-control" placeholder=""></textarea>
                    </div>
                </div>

            </div>

            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Note">
                        Note
                    </label>
                    <div class="col-sm-8">
                        <textarea id="Note" v-model="note" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-outline-primary" type="submit">Preview Sql</button>
            </div>
        </div>
    </form>
</template>

<style lang="scss">
</style>
