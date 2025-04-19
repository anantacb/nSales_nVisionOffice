<script setup>
import {onMounted, ref, watch} from "vue";
import router from "@/router";
import GeneralForm from "@/components/ui/FormElements/GeneralForm.vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useFormErrors} from "@/composables/useFormErrors";
import useCompanyInfos from "@/composables/useCompanyInfos";

import Customer from "@/models/Company/Customer";
import TableHelper from "@/models/TableHelper";
import User from "@/models/Office/User";
import useGeneralCreate from "@/composables/useGeneralCreate";
import _ from "lodash";
import CompanyLanguage from "@/models/Company/CompanyLanguage";

const createCustomerRef = ref(null);
const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

const {errors, setErrors, resetErrors} = useFormErrors();
const {isModuleEnabled} = useCompanyInfos();

const {
    ModelObject,
    GroupedTableFields,
    setOverriddenProperties,
    setExceptColumns,
    setCustomSortKeys,
    setNullHeaderText,
    getTableDetails,
    getCompanyAllTableFields
} = useGeneralCreate();

const PriceGroupOptions = ref([]);

async function getPriceGroups() {
    let {data} = await TableHelper.getColumnDistinctValues('Company', 'Pricegroup', 'Pricegroup', companyStore.selectedCompany.Id);
    if (!!data.length) {
        PriceGroupOptions.value.push({label: "Select Pricegroup", value: ""});
        data.map((item) => {
            PriceGroupOptions.value.push({label: item, value: item});
        });
    }
}

const CompanyUserOptions = ref([]);

async function getAllCompanyUsers() {
    const {data} = await User.getAllCompanyUsers(companyStore.selectedCompany.Id, true);
    let options = [{label: 'Select User', value: ''}];
    data.forEach((company_user) => {
        let option = {label: company_user.user.Name, value: company_user.Initials};
        options.push(option);
    });
    CompanyUserOptions.value = options;
}

const CompanyLanguageOptions = ref([]);

async function getAllCompanyLanguages() {
    const {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id, true);
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Code};
        options.push(option);
    });
    CompanyLanguageOptions.value = options;
}

function initFormValues() {
    setExceptColumns(['Id', 'InsertTime', 'UpdateTime', 'DeleteTime', 'UUID', 'ImportTime']);
    setNullHeaderText('Information');
    setCustomSortKeys(['General', 'Account', 'Sales', 'Contact', 'Misc', 'Info']);
    setOverriddenProperties({
        'Account': {
            HasTooltip: true,
            TooltipText: "This is the Account number for this customer. This will be the identifier of the customer.",
            Nullable: false,
        },
        'Currency': {
            HasTooltip: true,
            TooltipText: "This is the currency for this customer. This will make sure that the customer always see prices in the correct currency.",
            Nullable: false,
            DefaultValue: 'DKK'
        },
        'Pricegroup': {
            HasTooltip: true,
            TooltipText: "This is the currency for this customer. This will make sure that the customer always see prices in the correct currency.",
            SelectOptions: PriceGroupOptions.value
        },
        'Employee': {
            SelectOptions: CompanyUserOptions.value
        },
        'Language': {
            SelectOptions: CompanyLanguageOptions.value,
            Nullable: false,
        }
    });
}

async function createCustomer() {
    createCustomerRef.value.statusLoading();

    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        ...ModelObject.value
    };

    try {
        let {data, message} = await Customer.create(formData);
        createCustomerRef.value.statusNormal();
        await router.push({name: 'customers'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createCustomerRef.value.statusNormal();
    }
}

onMounted(async () => {
    createCustomerRef.value.statusLoading();
    await getTableDetails('Customer');
    isModuleEnabled('Pricegroup') ? await getPriceGroups() : PriceGroupOptions.value = [];
    await getAllCompanyUsers();
    await getAllCompanyLanguages();
    initFormValues();
    await getCompanyAllTableFields();
    createCustomerRef.value.statusNormal();
});

watch(() => companyStore.getSelectedCompany, async (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        createCustomerRef.value.statusLoading();
        await getTableDetails('Customer');
        isModuleEnabled('Pricegroup') ? await getPriceGroups() : PriceGroupOptions.value = [];
        await getAllCompanyUsers();
        await getAllCompanyLanguages();
        initFormValues();
        await getCompanyAllTableFields();
        createCustomerRef.value.statusNormal();
    }
});

</script>

<template>
    <div class="content">
        <BaseBlock ref="createCustomerRef" :title="`Create Customer (${companyStore.selectedCompany.Name})`"
                   content-full>
            <template #options>
                <BackButton :routerName="`customers`"/>
            </template>
            <GeneralForm :Errors="errors"
                         :GroupedTableFields="GroupedTableFields"
                         :ModelObject="ModelObject"
                         @formAction="createCustomer"
                         @resetErrors="resetErrors">
            </GeneralForm>
        </BaseBlock>
    </div>
</template>
