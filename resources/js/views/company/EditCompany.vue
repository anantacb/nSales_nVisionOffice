<script setup>
import {onMounted, ref} from "vue";
import TableHelper from "@/models/TableHelper";
import Company from "@/models/Office/Company";
import FlatPicker from "vue-flatpickr-component";
import {booleanOptions, countryOptions, cultureOptions, currencyOptions} from "@/data/dropDownOptions";
import slugify from "@sindresorhus/slugify";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import ModulePackage from "@/models/Office/ModulePackage";
import CompanyCustomDomain from "@/components/company/CompanyCustomDomain.vue";
import CompanyPostmarkServer from "@/components/company/CompanyPostmarkServer.vue";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let CompanyModel = ref({});

let storageLocationOptions = ref([]);
let typeOptions = ref([]);
let integrationTypeOptions = ref([]);
let fileTransferTypeOptions = ref([]);

async function getOptions() {
    let {data: typeData} = await TableHelper.getEnumValues('Office', `Company`, 'Type');
    typeOptions.value = typeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });

    let {data: integrationTypeData} = await ModulePackage.getAllModulePackages();
    integrationTypeOptions.value = integrationTypeData.map((item) => {
        return {
            label: item.Name,
            value: item.Name,
        }
    });

    let {data: fileTransferTypeData} = await TableHelper.getEnumValues('Office', `Company`, 'FileTransferType');
    fileTransferTypeOptions.value = fileTransferTypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });

    let {data: storageLocationData} = await TableHelper.getEnumValues('Office', `Company`, 'StorageLocation');
    storageLocationOptions.value = storageLocationData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

const updateCompanyRef = ref(null);

async function updateCompany() {
    updateCompanyRef.value.statusLoading();
    let formData = {
        Id: CompanyModel.value.Id,
        Name: CompanyModel.value.Name,
        Type: CompanyModel.value.Type,
        IntegrationType: CompanyModel.value.IntegrationType,
        FileTransferType: CompanyModel.value.FileTransferType,
        DomainName: CompanyModel.value.DomainName,
        DatabaseName: CompanyModel.value.DatabaseName,
        StorageLocation: CompanyModel.value.StorageLocation,
        CompanyName: CompanyModel.value.CompanyName,
        Street: CompanyModel.value.Street,
        ZipCode: CompanyModel.value.ZipCode,
        City: CompanyModel.value.City,
        State: CompanyModel.value.State,
        Country: CompanyModel.value.Country,
        VATNo: CompanyModel.value.VATNo,
        Email: CompanyModel.value.Email,
        PhoneNo: CompanyModel.value.PhoneNo,
        FaxNo: CompanyModel.value.FaxNo,
        ContactPerson: CompanyModel.value.ContactPerson,
        ContactEmail: CompanyModel.value.ContactEmail,
        Homepage: CompanyModel.value.Homepage,
        Seats: CompanyModel.value.Seats,
        NvisionMobileLicences: CompanyModel.value.NvisionMobileLicences,
        NsalesOfficeLicences: CompanyModel.value.NsalesOfficeLicences,
        DefaultCulture: CompanyModel.value.DefaultCulture,
        DefaultCurrency: CompanyModel.value.DefaultCurrency,
        TrialStartDate: CompanyModel.value.TrialStartDate,
        TrialDays: CompanyModel.value.TrialDays,
        Disabled: CompanyModel.value.Disabled,
        Note: CompanyModel.value.Note,
        ServiceUrl: CompanyModel.value.ServiceUrl,
        GraphQLServiceURL: CompanyModel.value.GraphQLServiceURL,
    };

    try {
        let {data, message} = await Company.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateCompanyRef.value.statusNormal();
}


const InitialCompanyModel = ref({});

async function getCompanyDetails() {
    let {data} = await Company.details(route.params.id);
    InitialCompanyModel.value = JSON.parse(JSON.stringify(data));

    CompanyModel.value = data;
    if (CompanyModel.value.TrialStartDate === "0000-00-00 00:00:00") {
        CompanyModel.value.TrialStartDate = null;
    }
}

onMounted(async () => {
    updateCompanyRef.value.statusLoading();
    await getOptions();
    await getCompanyDetails();
    updateCompanyRef.value.statusNormal();
});

function nameChanged() {
    if (InitialCompanyModel.value.Name === CompanyModel.value.Name) {
        CompanyModel.value.DomainName = InitialCompanyModel.value.DomainName;
        CompanyModel.value.DatabaseName = InitialCompanyModel.value.DatabaseName;
        return;
    }

    CompanyModel.value.DomainName = slugify(CompanyModel.value.Name, {
        separator: '',
        customReplacements: [
            ['&', '']
        ]
    });
    CompanyModel.value.DatabaseName = `nvisiondb_${CompanyModel.value.DomainName}`;
}

</script>

<template>
    <div class="content">
        <div class="row">
            <div class="col-12">
                <BaseBlock ref="updateCompanyRef" content-full title="Edit Company">

                    <template #options>
                        <router-link :to="{name:'companies'}" class="btn btn-sm btn-outline-info">
                            <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                        </router-link>
                    </template>

                    <form class="space-y-4" @submit.prevent="updateCompany">

                        <div class="row">
                            <div class="col-lg-4 space-y-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                        Account Name<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Name" v-model="CompanyModel.Name"
                                               :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                               autocomplete="off" class="form-control" name="Name"
                                               placeholder="Name"
                                               required
                                               type="text"
                                               @keyup="nameChanged"
                                        />
                                        <InputErrorMessages v-if="errors.Name"
                                                            :errorMessages="errors.Name"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                        Type<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="Type" v-model="CompanyModel.Type" :options="typeOptions"
                                                :required="true"
                                                :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="Type"/>
                                        <InputErrorMessages v-if="errors.Type"
                                                            :errorMessages="errors.Type"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="IntegrationType">
                                        Integration Type<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="IntegrationType" v-model="CompanyModel.IntegrationType"
                                                :options="integrationTypeOptions"
                                                :required="true"
                                                :select-class="errors.IntegrationType ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="IntegrationType"/>
                                        <InputErrorMessages v-if="errors.IntegrationType"
                                                            :errorMessages="errors.IntegrationType"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="FileTransferType">
                                        File Transfer Type<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="FileTransferType" v-model="CompanyModel.FileTransferType"
                                                :options="fileTransferTypeOptions"
                                                :required="true"
                                                :select-class="errors.FileTransferType ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="FileTransferType"/>
                                        <InputErrorMessages v-if="errors.FileTransferType"
                                                            :errorMessages="errors.FileTransferType"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="DomainName">
                                        Domain Name<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="DomainName" v-model="CompanyModel.DomainName"
                                               :class="errors.DomainName ? `is-invalid form-select-sm` : `form-select-sm`"
                                               class="form-control" disabled
                                               name="DomainName" required
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.DomainName"
                                                            :errorMessages="errors.DomainName"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="DatabaseName">
                                        Database Name<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="DatabaseName" v-model="CompanyModel.DatabaseName"
                                               :class="errors.DatabaseName ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" disabled name="DatabaseName"
                                               placeholder="" required
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.DatabaseName"
                                                            :errorMessages="errors.DatabaseName"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="StorageLocation">
                                        Storage Location<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="StorageLocation" v-model="CompanyModel.StorageLocation"
                                                :options="storageLocationOptions"
                                                :required="true"
                                                :select-class="errors.StorageLocation ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="StorageLocation"/>
                                        <InputErrorMessages v-if="errors.StorageLocation"
                                                            :errorMessages="errors.StorageLocation"></InputErrorMessages>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 space-y-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="CompanyName">
                                        Company Name<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="CompanyName" v-model="CompanyModel.CompanyName"
                                               :class="errors.CompanyName ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="CompanyName"
                                               required
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.CompanyName"
                                                            :errorMessages="errors.CompanyName"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Street">
                                        Address
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Street" v-model="CompanyModel.Street"
                                               :class="errors.Street ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Street"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.Street"
                                                            :errorMessages="errors.Street"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="ZipCode">
                                        ZipCode
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="ZipCode" v-model="CompanyModel.ZipCode"
                                               :class="errors.ZipCode ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="ZipCode"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.ZipCode"
                                                            :errorMessages="errors.ZipCode"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="City">
                                        City
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="City" v-model="CompanyModel.City"
                                               :class="errors.City ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="City"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.City"
                                                            :errorMessages="errors.City"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="State">
                                        State
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="State" v-model="CompanyModel.State"
                                               :class="errors.State ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="State"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.State"
                                                            :errorMessages="errors.State"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Country">
                                        Country
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="Country" v-model="CompanyModel.Country" :options="countryOptions"
                                                :select-class="errors.Country ? `is-invalid form-select-sm` : `form-select-sm`"
                                                autocomplete="off" name="Country"/>
                                        <InputErrorMessages v-if="errors.Country"
                                                            :errorMessages="errors.Country"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="VATNo">
                                        VAT No
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="VATNo" v-model="CompanyModel.VATNo"
                                               :class="errors.VATNo ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="VATNo"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.VATNo"
                                                            :errorMessages="errors.VATNo"></InputErrorMessages>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 space-y-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Email">
                                        Email
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Email" v-model="CompanyModel.Email"
                                               :class="errors.Email ? `is-invalid form-control-sm` : `form-control-sm`"
                                               autocomplete="off" class="form-control" name="Email"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.Email"
                                                            :errorMessages="errors.Email"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="PhoneNo">
                                        Phone No
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="PhoneNo" v-model="CompanyModel.PhoneNo"
                                               :class="errors.PhoneNo ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="PhoneNo"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.PhoneNo"
                                                            :errorMessages="errors.PhoneNo"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="FaxNo">
                                        Fax No
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="FaxNo" v-model="CompanyModel.FaxNo"
                                               :class="errors.FaxNo ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="FaxNo"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.FaxNo"
                                                            :errorMessages="errors.FaxNo"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="ContactPerson">
                                        Contact Person
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="ContactPerson" v-model="CompanyModel.ContactPerson"
                                               :class="errors.ContactPerson ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="ContactPerson"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.ContactPerson"
                                                            :errorMessages="errors.ContactPerson"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="ContactEmail">
                                        Contact Email
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="ContactEmail" v-model="CompanyModel.ContactEmail"
                                               :class="errors.ContactEmail ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="ContactEmail"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.ContactEmail"
                                                            :errorMessages="errors.ContactEmail"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Homepage">
                                        Homepage
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Homepage" v-model="CompanyModel.Homepage"
                                               :class="errors.Homepage ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="Homepage"
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.Homepage"
                                                            :errorMessages="errors.Homepage"></InputErrorMessages>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-4 space-y-2">
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Seats">
                                        Seats
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="Seats" v-model.number="CompanyModel.Seats"
                                               :class="errors.Seats ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" min="1"
                                               name="Seats"
                                               required type="number"/>
                                        <InputErrorMessages v-if="errors.Seats"
                                                            :errorMessages="errors.Seats"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm"
                                           for="NvisionMobileLicences">
                                        NvisionMobileLicences
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="NvisionMobileLicences"
                                               v-model.number="CompanyModel.NvisionMobileLicences"
                                               :class="errors.NvisionMobileLicences ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" min="1"
                                               name="NvisionMobileLicences"
                                               required type="number"/>
                                        <InputErrorMessages v-if="errors.NvisionMobileLicences"
                                                            :errorMessages="errors.NvisionMobileLicences"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="NsalesOfficeLicences">
                                        NsalesOfficeLicences
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="NsalesOfficeLicences"
                                               v-model.number="CompanyModel.NsalesOfficeLicences"
                                               :class="errors.NsalesOfficeLicences ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" min="1"
                                               name="NsalesOfficeLicences"
                                               required type="number"/>
                                        <InputErrorMessages v-if="errors.NsalesOfficeLicences"
                                                            :errorMessages="errors.NsalesOfficeLicences"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="DefaultCulture">
                                        Default Culture
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="DefaultCulture" v-model="CompanyModel.DefaultCulture"
                                                :options="cultureOptions"
                                                :required="true"
                                                :select-class="errors.DefaultCulture ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="DefaultCulture"/>
                                        <InputErrorMessages v-if="errors.DefaultCulture"
                                                            :errorMessages="errors.DefaultCulture"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="DefaultCurrency">
                                        Default Currency
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="DefaultCurrency" v-model="CompanyModel.DefaultCurrency"
                                                :options="currencyOptions"
                                                :required="true"
                                                :select-class="errors.DefaultCurrency ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="DefaultCulture"/>
                                        <InputErrorMessages v-if="errors.DefaultCurrency"
                                                            :errorMessages="errors.DefaultCurrency"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="TrialStartDate">
                                        Trial Start Date
                                    </label>
                                    <div class="col-sm-8">
                                        <FlatPicker
                                            id="TrialStartDate"
                                            v-model="CompanyModel.TrialStartDate"
                                            class="form-control form-control-sm"
                                            placeholder="Y-m-d"
                                        />
                                        <InputErrorMessages v-if="errors.TrialStartDate"
                                                            :errorMessages="errors.TrialStartDate"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="TrialDays">
                                        Trial Days<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input id="TrialDays" v-model.number="CompanyModel.TrialDays"
                                               :class="errors.TrialDays ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="TrialDays"
                                               required
                                               step="0" type="number"/>
                                        <InputErrorMessages v-if="errors.TrialDays"
                                                            :errorMessages="errors.TrialDays"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="Disabled">
                                        Disabled<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <Select id="Disabled" v-model="CompanyModel.Disabled" :options="booleanOptions"
                                                :required="true"
                                                :select-class="errors.Disabled ? `is-invalid form-select-sm` : `form-select-sm`"
                                                name="Disabled"/>
                                        <InputErrorMessages v-if="errors.Disabled"
                                                            :errorMessages="errors.Disabled"></InputErrorMessages>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-8 space-y-2">
                                <div class="row">
                                    <label class="col-sm-2 col-form-label col-form-label-sm" for="ServiceUrl">
                                        ServiceUrl<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input id="ServiceUrl" v-model="CompanyModel.ServiceUrl"
                                               :class="errors.ServiceUrl ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="ServiceUrl"
                                               required
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.ServiceUrl"
                                                            :errorMessages="errors.ServiceUrl"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-2 col-form-label col-form-label-sm" for="GraphQLServiceURL">
                                        GraphQLServiceURL<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input id="GraphQLServiceURL" v-model="CompanyModel.GraphQLServiceURL"
                                               :class="errors.GraphQLServiceURL ? `is-invalid form-control-sm` : `form-control-sm`"
                                               class="form-control" name="GraphQLServiceURL"
                                               required
                                               type="text"/>
                                        <InputErrorMessages v-if="errors.GraphQLServiceURL"
                                                            :errorMessages="errors.GraphQLServiceURL"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-2 col-form-label col-form-label-sm" for="Note">
                                        Note
                                    </label>
                                    <div class="col-sm-10">
                                <textarea id="Note" v-model="CompanyModel.Note"
                                          :class="errors.Note ? `is-invalid form-control-sm` : `form-control-sm`"
                                          class="form-control" name="Note"
                                ></textarea>
                                        <InputErrorMessages v-if="errors.Note"
                                                            :errorMessages="errors.Note"></InputErrorMessages>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

                    </form>

                </BaseBlock>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <CompanyCustomDomain v-if="CompanyModel.CustomDomainsArray" :CompanyId="route.params.id"/>
            </div>
            <div class="col-6">
                <CompanyPostmarkServer :CompanyId="route.params.id"
                                       :PostmarkServer="CompanyModel.postmark_email_server"/>
            </div>
        </div>
    </div>
</template>
