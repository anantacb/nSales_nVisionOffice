<script setup>

import {onMounted, ref} from "vue";

import Select from "@/components/ui/FormElements/Select.vue";
import InputErrorMessages from "@/components/ui/FormElements/InputErrorMessages.vue";
import VueSelect from 'vue-select';

import Module from "@/models/Office/Module";
import Company from "@/models/Office/Company";
import Table from "@/models/Office/Table";
import {useTemplateStore} from "@/stores/templateStore";
import {booleanOptions, clientSyncOptions, databaseOptions, typeOptions} from "@/data/dropDownOptions";

const templateStore = useTemplateStore();

const emit = defineEmits(['showPreview']);

let modules = ref();
let companies = ref([]);

// Form Values
let name = ref("");
let type = ref("Server");
let database = ref("Company");
let module = ref("");
let selectedCompanies = ref([]);
let disabled = ref(0);

let clientSync = ref("");
let autoNumbering = ref(0);
let enableTruncate = ref(1);

let sqlTruncate = ref("");
let sqlSeed = ref("");
let note = ref("");


let loading = ref({
    moduleDropDown: false,
    companiesDropDown: false
});

let errors = ref({});

onMounted(() => {
    getAllModules();
    getModuleEnabledCompanies();
});

async function getAllModules() {
    loading.value.moduleDropDown = true;
    let {data} = await Module.getAllModules();
    modules.value = data;
    loading.value.moduleDropDown = false;
}

async function getModuleEnabledCompanies() {
    selectedCompanies.value = [];
    if (!module.value) {
        companies.value = [];
        return;
    }
    loading.value.companiesDropDown = true;
    let data = await Company.getModuleEnabledCompanies(module.value);
    companies.value = data.data;
    loading.value.companiesDropDown = false;
}

async function submit() {
    templateStore.pageLoader({mode: "on"});
    let formData = {
        name: name.value,
        type: type.value,
        database: database.value,
        module: module.value,
        selectedCompanies: selectedCompanies.value,
        disabled: disabled.value,
        clientSync: clientSync.value,
        autoNumbering: autoNumbering.value,
        enableTruncate: enableTruncate.value,
        sqlTruncate: sqlTruncate.value,
        sqlSeed: sqlSeed.value,
        note: note.value
    };
    try {
        let {data} = await Table.getCreatePreviewSql(formData);
        emit("showPreview", data, formData);
    } catch (err) {
        if (err.response.status === 422) {
            errors.value = err.response.data.errors;
        }
    }
    templateStore.pageLoader({mode: "off"});
}

</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div class="row">
            <div class="col-lg-6 space-y-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Name">
                        Name
                    </label>
                    <div class="col-sm-8">
                        <input id="Name" v-model="name" :class="{'is-invalid': errors.name}"
                               class="form-control" name="Name" placeholder="Name"
                               required
                               type="text"/>
                        <InputErrorMessages v-if="errors.name" :errorMessages="errors.name"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Type">
                        Type
                    </label>
                    <div class="col-sm-8">
                        <Select id="Type" v-model="type" :options="typeOptions"
                                :required="true"
                                :select-class="errors.type ? `is-invalid` : ``" name="Type"/>
                        <InputErrorMessages v-if="errors.type" :errorMessages="errors.type"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Database">
                        Database
                    </label>
                    <div class="col-sm-8">
                        <Select id="Database" v-model="database" :options="databaseOptions"
                                :required="true" :select-class="errors.database ? `is-invalid` : ``"
                                name="Database"/>
                        <InputErrorMessages v-if="errors.database"
                                            :errorMessages="errors.database"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Module">
                        Module
                    </label>
                    <div class="col-sm-8">
                        <VueSelect
                            v-model="module"
                            :class="{'is-invalid': errors.module}"
                            :clearable="false"
                            :get-option-label="module => module.Name"
                            :input-required="true"
                            :inputId="`Module`"
                            :inputName="`Module`"
                            :loading="loading.moduleDropDown"
                            :options="modules"
                            :reduce="module => module.Id"
                            :searchable="true"
                            placeholder="Select Module"
                            @option:selected="getModuleEnabledCompanies"
                        >
                            <template #search="{attributes, events}">
                                <input
                                    :required="!module"
                                    class="vs__search"
                                    v-bind="attributes"
                                    v-on="events"
                                />
                            </template>
                        </VueSelect>
                        <InputErrorMessages v-if="errors.module" :errorMessages="errors.module"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Companies">
                        Companies
                    </label>
                    <div class="col-sm-8">
                        <VueSelect
                            v-model="selectedCompanies"
                            :class="{'is-invalid': errors.selectedCompanies}"
                            :clearable="true"
                            :get-option-label="company => company.Name"
                            :inputId="`Companies`"
                            :loading="loading.companiesDropDown"
                            :options="companies"
                            :reduce="company => company.Id"
                            :searchable="true"
                            multiple
                            placeholder="Select Companies">
                        </VueSelect>
                        <InputErrorMessages v-if="errors.selectedCompanies"
                                            :errorMessages="errors.selectedCompanies"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Disabled">
                        Disabled
                    </label>
                    <div class="col-sm-8">
                        <Select id="Disabled" v-model="disabled" :options="booleanOptions" :required="true"
                                :select-class="errors.disabled ? `is-invalid` : ``"
                                name="Disabled"/>
                        <InputErrorMessages v-if="errors.disabled"
                                            :errorMessages="errors.disabled"></InputErrorMessages>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 space-y-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="ClientSync">
                        Client Sync
                    </label>
                    <div class="col-sm-8">
                        <Select id="ClientSync" v-model="clientSync" :options="clientSyncOptions"
                                :required="false" :select-class="errors.clientSync ? `is-invalid` : ``"
                                name="ClientSync"/>
                        <InputErrorMessages v-if="errors.clientSync"
                                            :errorMessages="errors.clientSync"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="AutoNumbering">
                        Auto Numbering
                    </label>
                    <div class="col-sm-8">
                        <Select id="AutoNumbering" v-model="autoNumbering" :options="booleanOptions" :required="true"
                                :select-class="errors.autoNumbering ? `is-invalid` : ``" name="AutoNumbering"/>
                        <InputErrorMessages v-if="errors.autoNumbering"
                                            :errorMessages="errors.autoNumbering"></InputErrorMessages>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="example-hf-password">
                        Enable Truncate:
                    </label>
                    <div class="col-sm-8">
                        <Select v-model="enableTruncate" :options="booleanOptions" :required="true"
                                :select-class="errors.enableTruncate ? `is-invalid` : ``"/>
                        <InputErrorMessages v-if="errors.enableTruncate"
                                            :errorMessages="errors.enableTruncate"></InputErrorMessages>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="">
                        Sql Truncate
                    </label>
                    <div class="col-sm-8">
                        <textarea v-model="sqlTruncate" :class="{'is-invalid': errors.sqlTruncate}" class="form-control"
                                  placeholder=""></textarea>
                        <InputErrorMessages v-if="errors.sqlTruncate"
                                            :errorMessages="errors.sqlTruncate"></InputErrorMessages>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="SqlSeed">
                        Sql Seed
                    </label>
                    <div class="col-sm-8">
                        <textarea id="SqlSeed" v-model="sqlSeed" :class="{'is-invalid': errors.sqlSeed}"
                                  class="form-control" placeholder=""></textarea>
                        <InputErrorMessages v-if="errors.sqlSeed" :errorMessages="errors.sqlSeed"></InputErrorMessages>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 space-y-3 mt-3">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="Note">
                        Note
                    </label>
                    <div class="col-sm-8">
                        <textarea id="Note" v-model="note" :class="{'is-invalid': errors.note}"
                                  class="form-control"></textarea>
                        <InputErrorMessages v-if="errors.note" :errorMessages="errors.note"></InputErrorMessages>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-outline-primary" type="submit">Preview Sql</button>
            </div>
        </div>
    </form>
</template>
