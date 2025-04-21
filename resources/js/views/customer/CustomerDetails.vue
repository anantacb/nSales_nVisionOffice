<script setup>
import {onMounted, ref} from "vue";
import User from "@/models/Office/User";
import TableHelper from "@/models/TableHelper";
import Order from "@/models/Company/Order";
import Customer from "@/models/Company/Customer";
import {useAuthStore} from "@/stores/authStore";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormatter} from "@/composables/useFormatter";
import {useFormErrors} from "@/composables/useFormErrors";
import useGeneralCreate from "@/composables/useGeneralCreate";
import GeneralForm from "@/components/ui/FormElements/GeneralForm.vue";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useRoute} from "vue-router";
import router from "@/router";
import CompanyLanguage from "@/models/Company/CompanyLanguage";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();

const PriceGroupOptions = ref([]);
const CompanyUserOptions = ref([]);
const CompanyLanguageOptions = ref([]);
let LatestOrderModel = ref({});

let dateFormatStr = ref('DD, MMM YYYY');
let createCustomerRef = ref(null);
let customerStatus = ref('(InActive)');
let backButtonRoute = localStorage.getItem('customer-details-back-route') ?? 'customers';

const {
    ModelObject,
    GroupedTableFields,
    setOverriddenProperties,
    setModelObject,
    setExceptColumns,
    setCustomSortKeys,
    setNullHeaderText,
    getTableDetails,
    getCompanyAllTableFields
} = useGeneralCreate();

function isCustomerActive() {
    customerStatus.value = !ModelObject.value.Blocked ? '(Active)' : '(InActive)';
}

function initFormValues() {
    setExceptColumns(['Id', 'InsertTime', 'UpdateTime', 'DeleteTime', 'UUID', 'ImportTime']);
    setNullHeaderText('Information');
    setCustomSortKeys(['General', 'Account', 'Sales', 'Contact', 'Misc', 'Info']);
    setOverriddenProperties({
        'Account': {
            InputLocked: true,
            HasTooltip: true,
            TooltipText: "This is the Account number for this customer. This will be the identifier of the customer.",
            Nullable: false
        },
        'Currency': {
            HasTooltip: true,
            TooltipText: "This is the currency for this customer. This will make sure that the customer always see prices in the correct currency.",
            DefaultValue: 'DKK',
            Nullable: false
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
            Nullable: false
        }
    });
}

async function getPriceGroups() {
    let {data} = await TableHelper.getColumnDistinctValues('Company', 'Pricegroup', 'Pricegroup', companyStore.selectedCompany.Id);
    if (!!data.length) {
        PriceGroupOptions.value.push({label: "Please Select", value: ""});
        data.map((item) => {
            PriceGroupOptions.value.push({label: item, value: item});
        });
    }
}

async function getAllCompanyUsers() {
    const {data} = await User.getAllCompanyUsers(companyStore.selectedCompany.Id, true);
    let options = [{label: 'Select User', value: ''}];

    data.forEach((company_user) => {
        let option = {label: company_user.user.Name, value: company_user.Initials};
        options.push(option);
    });

    CompanyUserOptions.value = options;
}

async function getAllCompanyLanguages() {
    const {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id, true);
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Code};
        options.push(option);
    });
    CompanyLanguageOptions.value = options;
}

async function getCustomerLatestOrders() {
    let {data} = await Order.getLatestOrdersByCustomer(companyStore.selectedCompany.Id, route.params.id);
    LatestOrderModel.value = data;
}

async function getCustomerDetails() {
    let {data} = await Customer.details(companyStore.selectedCompany.Id, route.params.id);
    setModelObject(data);
}

async function updateCustomerInfo() {
    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        ...ModelObject.value
    };

    try {
        let {data, message} = await Customer.update(formData);
        createCustomerRef.value.statusNormal();
        await router.push({name: 'customers'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createCustomerRef.value.statusNormal();
    }
}

onMounted(async () => {
    await getCustomerLatestOrders();
    createCustomerRef.value.statusLoading();
    await getCustomerDetails();
    await getTableDetails('Customer');
    isModuleEnabled('Pricegroup') ? await getPriceGroups() : PriceGroupOptions.value = [];
    await getAllCompanyUsers();
    await getAllCompanyLanguages();
    initFormValues();
    await getCompanyAllTableFields();
    isCustomerActive();
    createCustomerRef.value.statusNormal();
});
</script>

<template>
    <!-- Content Heading -->
    <BaseContentHeading :subtitle="ModelObject.Name+' '+customerStatus">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="mt-3 mt-md-0 ms-md-3 space-x-1">
                        <BackButton :routerName="backButtonRoute"/>
                    </li>

                </ol>
            </nav>

        </template>
    </BaseContentHeading>

    <!-- Page Content -->
    <div class="content pt-0">

        <!-- Order Lists -->
        <BaseBlock title="Latest Orders">
            <template #content>
                <div class="block-content block-content-full">
                    <div v-if="LatestOrderModel.length" class="table-responsive order-list-div">
                        <table class="table table-striped table-vcenter order-list-table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Order No.</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="fs-sm">
                            <tr v-for="order of LatestOrderModel">
                                <td>{{ dateFormat(order.OrderDate, dateFormatStr) }}</td>
                                <td>{{ order.Type }}</td>
                                <td>{{ order.OrderNumber }}</td>
                                <td>{{ numberFormat(order.TotalExVat) }} {{ order.CustomerCurrency }}</td>
                                <td>{{ order.ExportStatus }}</td>
                                <td>
                                    <router-link :to="`/order/${order.UUID}`">
                                        <i class="fas fa-external-link-alt"></i>
                                    </router-link>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else>No recent order.</div>
                </div>
            </template>
        </BaseBlock>
        <!-- END Order Lists -->

        <!-- Customer Info -->
        <BaseBlock ref="createCustomerRef" title="Customer Info">
            <template #content>
                <div class="block-content block-content-full">
                    <GeneralForm :Errors="errors"
                                 :FormType="`Update`"
                                 :GroupedTableFields="GroupedTableFields"
                                 :ModelObject="ModelObject"
                                 @formAction="updateCustomerInfo"
                                 @resetErrors="resetErrors">
                    </GeneralForm>
                </div>

            </template>
        </BaseBlock>
        <!-- END Customer Info -->

    </div>
</template>

<style scoped>
.order-list-div {
    max-height: 250px;
    overflow: auto;
}

.table thead th {
    text-transform: none !important;
}

</style>
