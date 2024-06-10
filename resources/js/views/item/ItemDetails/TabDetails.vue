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
import Item from "@/models/Company/Item";
import WebShopLanguage from "@/models/Company/WebShopLanguage";
import ItemAttribute from "@/models/Company/ItemAttribute";
import BaseBlock from "@/components/BaseBlock.vue";
import ItemAttributes from "@/views/item/ItemDetails/TabAttributes.vue";
import ItemOverview from "@/views/item/ItemDetails/ItemOverview.vue";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setProductInfo']);

let TabDetailsRef = ref(null);

let showItemAttributeTab = ref(false);

const PriceGroupOptions = ref([]);
const CompanyUserOptions = ref([]);
let LatestOrderModel = ref({});

let dateFormatStr = ref('DD, MMM YYYY');
let createProductRef = ref(null);
let customerStatus = ref('(InActive)');
let backButtonRoute = localStorage.getItem('item-details-back-route') ?? 'items';

let WebShopLanguages = ref([]);
// let ItemAttributes = ref([]);


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
    setExceptColumns([
        'Id', 'InsertTime', 'UpdateTime', 'DeleteTime', 'UUID', 'ImportTime', 'PriceCost', 'PriceRecRetail',
        'Currency', 'VatRate', 'AllowOrderDiscount', 'SupplierName', 'MoreUrl', 'AltItemNumber', 'Image',
        'SortPriority', 'CollectionName', 'Description1', 'Description2', 'Description3', 'Description4',
        'IsShopifyUploading', 'ShopifySyncNeeded', 'Collection'
    ]);
    setNullHeaderText('Information');
    // setCustomSortKeys(['General', 'Account', 'Sales', 'Contact', 'Misc', 'Info']);
    setCustomSortKeys([]);
    setOverriddenProperties({
        'Number': {
            InputLocked: true,
            LabelTitle: 'Number/SKU',
            HasTooltip: true,
            TooltipText: "This is the Product number for this product. This will be the identifier of the Product.",
            InputRequired: true
        },
        'Colli': {
            LabelTitle: 'Colli/Pack Size',
        },
        // 'Employee': {
        //     SelectOptions: CompanyUserOptions.value
        // }
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

async function getCustomerLatestOrders() {
    let {data} = await Order.getLatestOrdersByCustomer(companyStore.selectedCompany.Id, route.params.id);
    LatestOrderModel.value = data;
}

async function getCustomerDetails() {
    let {data} = await Customer.details(companyStore.selectedCompany.Id, route.params.id);
    setModelObject(data);
}

async function getItemDetails() {
    // productInfoRef.value.statusLoading();
    let {data} = await Item.details(companyStore.selectedCompany.Id, route.params.id);
    setModelObject(data);
    emit('setProductInfo', data);
    // productInfoRef.value.statusNormal();
}

async function getAllWebShopLanguages() {
    let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
    console.log(data);
}

async function getProductItemAttributes() {
    let {data} = await ItemAttribute.fetchByItem(companyStore.selectedCompany.Id, route.params.id);
    console.log(data);
}

async function updateCustomerInfo() {
    alert('Under dev');
    return 0;

    let formData = {
        CompanyId: companyStore.selectedCompany.Id,
        ...ModelObject.value
    };

    try {
        let {data, message} = await Customer.update(formData);
        createProductRef.value.statusNormal();
        await router.push({name: 'customers'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createProductRef.value.statusNormal();
    }
}

function loadItemAttributeTab() {
    showItemAttributeTab.value = true;
}


onMounted(async () => {
    // await getCustomerLatestOrders();
    // createProductRef.value.statusLoading();
    // await getCustomerDetails();
    // await getTableDetails('Customer');
    // isModuleEnabled('Pricegroup') ? await getPriceGroups() : PriceGroupOptions.value = [];
    // await getAllCompanyUsers();
    // initFormValues();
    // await getCompanyAllTableFields();
    // isCustomerActive();
    // createProductRef.value.statusNormal();

    TabDetailsRef.value.statusLoading();
    // DetailsRef.value['Details'].statusLoading();

    await getItemDetails();
    await getTableDetails('Item');
    initFormValues();
    await getCompanyAllTableFields();


    TabDetailsRef.value.statusNormal();
    // DetailsRef.value['Details'].statusNormal();

    // isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguages.value = [];
    // isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];

});

</script>

<template>
    <BaseBlock ref="TabDetailsRef" content-full>
        <GeneralForm :Errors="errors"
                     :FormType="`Update`"
                     :GroupedTableFields="GroupedTableFields"
                     :ModelObject="ModelObject"
                     @formAction="updateCustomerInfo"
                     @resetErrors="resetErrors">
        </GeneralForm>
    </BaseBlock>
</template>

<style scoped>
</style>
