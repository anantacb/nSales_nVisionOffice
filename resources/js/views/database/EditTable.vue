<script setup>
import {onMounted, ref} from "vue";
import VueSelect from 'vue-select';

import Module from "@/models/Office/Module";
import Table from "@/models/Office/Table";
import {booleanOptions, clientSyncOptions, databaseOptions, typeOptions} from "@/data/dropDownOptions";
import {useRoute} from "vue-router";
import {useNotificationStore} from "@/stores/notificationStore";

const route = useRoute();
const notificationStore = useNotificationStore();

let modules = ref([]);
let companies = ref([]);

let updateTableRef = ref(null);
let TableModel = ref({});

let loading = ref({
    moduleDropDown: false,
    companiesDropDown: false
});

let errors = ref({});


onMounted(async () => {
    updateTableRef.value.statusLoading();
    await getTableDetails();
    updateTableRef.value.statusNormal();
});

async function update() {
    updateTableRef.value.statusLoading();
    let formData = {
        Id: TableModel.value.Id,
        Disabled: TableModel.value.Disabled,
        ClientSync: TableModel.value.ClientSync,
        AutoNumbering: TableModel.value.AutoNumbering,
        EnableSqlTruncate: TableModel.value.EnableSqlTruncate,
        SqlTruncate: TableModel.value.SqlTruncate,
        SqlSeed: TableModel.value.SqlSeed,
        Note: TableModel.value.Note
    };
    try {
        let {data, message} = await Table.update(formData);
        notificationStore.showNotification(message);
    } catch (err) {
        if (err.response.status === 422) {
            errors.value = err.response.data.errors;
        }
    } finally {
        updateTableRef.value.statusNormal();
    }
}

async function getTableDetails() {
    let {data} = await Table.getDetails(route.params.id);
    TableModel.value = data;

    let tableCompanies = data.company_tables.map((company_table) => {
        return {
            label: company_table.company.Name,
            value: company_table.company.Id
        }
    });

    _.orderBy(tableCompanies, ['label'], ['asc']);
}

</script>

<template>

    <div class="content">
        <BaseBlock ref="updateTableRef" :title="`Update Table (${TableModel.Name})`" content-full>
            <template #options>
                <router-link :to="{name:'tables'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="update">
                <div class="row space-y-2">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="TableModel.Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" disabled name="Name"
                                       placeholder="Name" required
                                       type="text"/>
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="TableModel.Type" :options="typeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        disabled
                                        name="Type"/>
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Database">
                                Database
                            </label>
                            <div class="col-sm-8">
                                <Select id="Database" v-model="TableModel.Database" :options="databaseOptions"
                                        :required="true"
                                        :select-class="errors.Database ? `is-invalid form-select-sm` : `form-select-sm`"
                                        disabled
                                        name="Database"
                                />
                                <InputErrorMessages v-if="errors.Database"
                                                    :errorMessages="errors.Database"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Module">
                                Module
                            </label>
                            <div class="col-sm-8">
                                <VueSelect
                                    v-model="TableModel.module"
                                    :class="errors.module ? `is-invalid` : ``"
                                    :clearable="false"
                                    :disabled="true"
                                    :get-option-label="Module => Module.Name"
                                    :input-required="true"
                                    :inputId="`Module`"
                                    :inputName="`Module`"
                                    :options="modules"
                                    :reduce="Module => Module.Id"
                                    :searchable="true"
                                    placeholder="Select Module"
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
                                <InputErrorMessages v-if="errors.module"
                                                    :errorMessages="errors.module"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Companies">
                                Companies
                            </label>
                            <div class="col-sm-8">
                                <VueSelect
                                    v-model="TableModel.company_tables"
                                    :class="errors.selectedCompanies ? `is-invalid` : ``"
                                    :clearable="true"
                                    :disabled="true"
                                    :get-option-label="company => company.company.Name"
                                    :inputId="`Companies`"
                                    :loading="loading.companiesDropDown"
                                    :options="companies"
                                    :reduce="company => company.CompanyId"
                                    :searchable="true"
                                    multiple
                                    placeholder="Select Companies"
                                >
                                </VueSelect>
                                <InputErrorMessages v-if="errors.selectedCompanies"
                                                    :errorMessages="errors.selectedCompanies"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Disabled">
                                Disabled
                            </label>
                            <div class="col-sm-8">
                                <Select id="Disabled" v-model="TableModel.Disabled" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.Disabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Disabled"/>
                                <InputErrorMessages v-if="errors.Disabled"
                                                    :errorMessages="errors.Disabled"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="ClientSync">
                                Client Sync
                            </label>
                            <div class="col-sm-8">
                                <Select id="ClientSync" v-model="TableModel.ClientSync" :options="clientSyncOptions"
                                        :required="false"
                                        :select-class="errors.ClientSync ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="ClientSync"/>
                                <InputErrorMessages v-if="errors.ClientSync"
                                                    :errorMessages="errors.ClientSync"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="AutoNumbering">
                                Auto Numbering
                            </label>
                            <div class="col-sm-8">
                                <Select id="AutoNumbering" v-model="TableModel.AutoNumbering" :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.AutoNumbering ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="AutoNumbering"/>
                                <InputErrorMessages v-if="errors.AutoNumbering"
                                                    :errorMessages="errors.AutoNumbering"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="EnableTruncate">
                                Enable Truncate:
                            </label>
                            <div class="col-sm-8">
                                <Select id="EnableTruncate" v-model="TableModel.EnableSqlTruncate"
                                        :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.EnableSqlTruncate ? `is-invalid form-select-sm` : `form-select-sm`"/>
                                <InputErrorMessages v-if="errors.EnableSqlTruncate"
                                                    :errorMessages="errors.EnableSqlTruncate"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SqlTruncate">
                                Sql Truncate
                            </label>
                            <div class="col-sm-8">
                        <textarea id="SqlTruncate"
                                  v-model="TableModel.SqlTruncate"
                                  :class="errors.SqlTruncate ? `is-invalid form-control-sm` : `form-control-sm`"
                                  class="form-control"
                                  placeholder=""></textarea>
                                <InputErrorMessages v-if="errors.SqlTruncate"
                                                    :errorMessages="errors.SqlTruncate"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="SqlSeed">
                                Sql Seed
                            </label>
                            <div class="col-sm-8">
                        <textarea id="SqlSeed" v-model="TableModel.SqlSeed"
                                  :class="errors.SqlSeed ? `is-invalid form-control-sm` : `form-control-sm`"
                                  class="form-control" placeholder=""></textarea>
                                <InputErrorMessages v-if="errors.SqlSeed"
                                                    :errorMessages="errors.SqlSeed"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Note">
                                Note
                            </label>
                            <div class="col-sm-8">
                        <textarea id="Note" v-model="TableModel.Note"
                                  :class="errors.Note ? `is-invalid form-control-sm` : `form-control-sm`"
                                  class="form-control"></textarea>
                                <InputErrorMessages v-if="errors.Note"
                                                    :errorMessages="errors.Note"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-outline-primary" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </BaseBlock>
    </div>
</template>
