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
import ItemAttributes from "@/views/item/ItemDetails/ItemAttributes.vue";
import ItemOverview from "@/views/item/ItemDetails/ItemOverview.vue";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setProductInfo']);

let productInfoRef = ref(null);

let TabRefs = ref({});
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

    TabRefs.value['Details'].statusLoading();
    // DetailsRef.value['Details'].statusLoading();

    await getItemDetails();
    await getTableDetails('Item');
    initFormValues();
    await getCompanyAllTableFields();


    TabRefs.value["Details"].statusNormal();
    // DetailsRef.value['Details'].statusNormal();

    // isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguages.value = [];
    // isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];

    // if (this.moduleEnabled('WSLanguage')) {
    //     this.getAllWebShopLanguages();
    // }
    //
    // if (this.moduleEnabled('Itemattribute')) {
    //     this.getProductItemAttributes();
    // }
    //
    // if (this.moduleEnabled('Pricegroup')) {
    //     this.getProductPriceGroups();
    // }
    //
    // if (this.moduleEnabled(`ItemVariantDescription`)) {
    //     this.getItemVariantDescriptions();
    // }

});

const currentTab = ref('Details');

const tabs = ref(['Details', 'Attributes', 'Translations', 'SEO', 'Pricegroups']);

function setTabRef(el, tab) {
    TabRefs.value[tab] = el;
    // return function (el) {
    //     TabRefs.value[tab] = el;
    // }
}

// const setTabRef = tab => el => {
//     TabRefs.value[tab] = el;
// };
</script>

<template>
    <BaseBlock>
        <template #content>
            <ul class="nav nav-tabs nav-tabs-block " role="tablist">
                <li v-for="tab in tabs"
                    :key="'tab-'+tab"
                    class="nav-item"
                >
                    <button
                        :id="`btabs-static-${tab}-tab`"
                        :aria-controls="`btabs-static-${tab}`"
                        :aria-selected="currentTab === tab"
                        :class="['nav-link', { active: currentTab === tab }]"
                        :data-bs-target="`#btabs-static-${tab}`"
                        data-bs-toggle="tab"
                        role="tab"
                        @click="currentTab = tab"
                    >
                        {{ tab }}
                    </button>
                </li>
            </ul>

            <!--tab-content-->
            <div class="block-content tab-content">
                <template v-for="(tab, index) in tabs" :key="'content-'+tab"
                >
                    <div
                        :id="`btabs-static-${tab}`"
                        :aria-labelledby="`btabs-static-${tab}-tab`"
                        :class="['tab-pane', { active: currentTab === tab }]"
                        role="tabpanel"
                        tabindex="0"
                    >
                        <h4 class="fw-normal">{{ tab }}</h4>
                        <!--                        <p>...</p>-->
                        <template v-if="tab === 'Details'">

                            <BaseBlock :ref="(el) => setTabRef(el, tab)" content-full>
                                <GeneralForm :Errors="errors"
                                             :FormType="`Update`"
                                             :GroupedTableFields="GroupedTableFields"
                                             :ModelObject="ModelObject"
                                             @formAction="updateCustomerInfo"
                                             @resetErrors="resetErrors">
                                </GeneralForm>
                            </BaseBlock>

                        </template>

                        <template v-if="tab === 'Attributes'">

                            <ItemAttributes>

                            </ItemAttributes>
                        </template>
                    </div>
                </template>
            </div>


            <!--                        <ul class="nav nav-tabs nav-tabs-block " role="tablist">-->
            <!--                            <li class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-details-tab"-->
            <!--                                    aria-controls="btabs-static-details"-->
            <!--                                    aria-selected="true"-->
            <!--                                    class="nav-link active"-->
            <!--                                    data-bs-target="#btabs-static-details"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                >-->
            <!--                                    Details-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                            <li v-if="isModuleEnabled('Itemattribute')" class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-attributes-tab"-->
            <!--                                    aria-controls="btabs-static-attributes"-->
            <!--                                    aria-selected="false"-->
            <!--                                    class="nav-link"-->
            <!--                                    data-bs-target="#btabs-static-attributes"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                    @click="showItemAttributeTab=true"-->
            <!--                                >-->
            <!--                                    Attributes-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                            <li v-if="isModuleEnabled('WebShop')" class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-translations-tab"-->
            <!--                                    aria-controls="btabs-static-translations"-->
            <!--                                    aria-selected="false"-->
            <!--                                    class="nav-link"-->
            <!--                                    data-bs-target="#btabs-static-translations"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                >-->
            <!--                                    Translations-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                            <li v-if="isModuleEnabled('WSSEO')" class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-seo-tab"-->
            <!--                                    aria-controls="btabs-static-seo"-->
            <!--                                    aria-selected="false"-->
            <!--                                    class="nav-link"-->
            <!--                                    data-bs-target="#btabs-static-seo"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                >-->
            <!--                                    SEO-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                            <li v-if="isModuleEnabled('Pricegroup')" class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-pricegroups-tab"-->
            <!--                                    aria-controls="btabs-static-pricegroups"-->
            <!--                                    aria-selected="false"-->
            <!--                                    class="nav-link"-->
            <!--                                    data-bs-target="#btabs-static-pricegroups"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                >-->
            <!--                                    Pricegroups-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                            <li class="nav-item">-->
            <!--                                <button-->
            <!--                                    id="btabs-static-productImage-tab"-->
            <!--                                    aria-controls="btabs-static-productImage"-->
            <!--                                    aria-selected="false"-->
            <!--                                    class="nav-link"-->
            <!--                                    data-bs-target="#btabs-static-productImage"-->
            <!--                                    data-bs-toggle="tab"-->
            <!--                                    role="tab"-->
            <!--                                >-->
            <!--                                    Product Image-->
            <!--                                </button>-->
            <!--                            </li>-->
            <!--                        </ul>-->

            <!--                        &lt;!&ndash;tab-content&ndash;&gt;-->
            <!--                        <div class="block-content tab-content">-->
            <!--                            <div-->
            <!--                                id="btabs-static-details"-->
            <!--                                aria-labelledby="btabs-static-details-tab"-->
            <!--                                class="tab-pane active"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->
            <!--                                <BaseBlock ref="productInfoRef" content-full>-->
            <!--                                    <GeneralForm :Errors="errors"-->
            <!--                                                 :FormType="`Update`"-->
            <!--                                                 :GroupedTableFields="GroupedTableFields"-->
            <!--                                                 :ModelObject="ModelObject"-->
            <!--                                                 @formAction="updateCustomerInfo"-->
            <!--                                                 @resetErrors="resetErrors">-->
            <!--                                    </GeneralForm>-->
            <!--                                </BaseBlock>-->
            <!--                            </div>-->
            <!--                            <div-->
            <!--                                id="btabs-static-attributes"-->
            <!--                                aria-labelledby="btabs-static-attributes-tab"-->
            <!--                                class="tab-pane"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->
            <!--                                <ItemAttributes v-if="showItemAttributeTab">-->

            <!--                                </ItemAttributes>-->

            <!--                            </div>-->
            <!--                            <div-->
            <!--                                id="btabs-static-translations"-->
            <!--                                aria-labelledby="btabs-static-translations-tab"-->
            <!--                                class="tab-pane"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->

            <!--                                <BaseBlock ref="" content-full>-->
            <!--                                    <h4 class="fw-normal">Translations Content</h4>-->
            <!--                                    <p>...</p>-->
            <!--                                </BaseBlock>-->
            <!--                            </div>-->
            <!--                            <div-->
            <!--                                id="btabs-static-seo"-->
            <!--                                aria-labelledby="btabs-static-seo-tab"-->
            <!--                                class="tab-pane"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->
            <!--                                <BaseBlock ref="" content-full>-->
            <!--                                    <h4 class="fw-normal">SEO Content</h4>-->
            <!--                                    <p>...</p>-->
            <!--                                </BaseBlock>-->
            <!--                            </div>-->
            <!--                            <div-->
            <!--                                id="btabs-static-pricegroups"-->
            <!--                                aria-labelledby="btabs-static-pricegroups-tab"-->
            <!--                                class="tab-pane"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->
            <!--                                <BaseBlock ref="" content-full>-->
            <!--                                    <h4 class="fw-normal">Pricegroups Content</h4>-->
            <!--                                    <p>...</p>-->
            <!--                                </BaseBlock>-->
            <!--                            </div>-->
            <!--                            <div-->
            <!--                                id="btabs-static-productImage"-->
            <!--                                aria-labelledby="btabs-static-productImage-tab"-->
            <!--                                class="tab-pane"-->
            <!--                                role="tabpanel"-->
            <!--                                tabindex="0"-->
            <!--                            >-->

            <!--                                <BaseBlock ref="" content-full>-->
            <!--                                    <h4 class="fw-normal">Image Content</h4>-->
            <!--                                    <p>...</p>-->

            <!--                                    <img v-if="ModelObject.image_urls"-->
            <!--                                         v-lazy="ModelObject.image_urls.Fullsize"-->
            <!--                                         class="img-responsive"-->
            <!--                                         width="500"-->
            <!--                                    >-->
            <!--                                </BaseBlock>-->
            <!--                            </div>-->
            <!--                        </div>-->
            <!--tab-content end-->
        </template>
    </BaseBlock>
</template>

<style scoped>
.block-content {
//padding: 0;
}
</style>
