<script setup>
import {onMounted, ref} from "vue";
import Order from "@/models/Company/Order";
import {useCompanyStore} from "@/stores/companyStore";
import {useFormatter} from "@/composables/useFormatter";
import {useRoute} from "vue-router";

const route = useRoute();
const companyStore = useCompanyStore();
let {numberFormat, dateFormat} = useFormatter();

let OrderModel = ref({});
let firstRowClass = ref('col-lg-6 col-xxl-6');
let dateFormatStr = ref('DD, MMM YYYY');
let backButtonRoute = localStorage.getItem('order-details-back-route') ?? '';

async function getOrderDetails() {
    let {data} = await Order.details(companyStore.selectedCompany.Id, route.params.id);

    OrderModel.value = data;

    if (OrderModel.value.web_shop_user) {
        firstRowClass.value = 'col-lg-4 col-xxl-4';
    }
}

onMounted(async () => {
    await getOrderDetails();
});

</script>

<template>
    <!-- Content Heading -->
    <BaseContentHeading title="Detail Orders">
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
    <!-- End Content Heading -->

    <!-- Page Content -->
    <div class="content pt-0">
        <div class="row items-push">
            <!-- Billing Details -->
            <div :class="firstRowClass">
                <BaseBlock class="d-flex flex-column mb-0" title="Billing">
                    <template #content>
                        <div class="block-rounded block mb-2">
                            <div class="block-content-full block-content">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 fs-sm">
                                        <tr>
                                            <th>Account</th>
                                            <td>{{ OrderModel.CustomerAccount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ OrderModel.CustomerBillingName }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ OrderModel.CustomerBillingAddress1 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Zipcode / City</th>
                                            <td>{{ OrderModel.CustomerBillingZipcode }}
                                                {{ OrderModel.CustomerBillingCity }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Country</th>
                                            <td>{{ OrderModel.CustomerBillingCountry }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ OrderModel.CustomerBillingEmail }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ OrderModel.CustomerBillingPhone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Attention</th>
                                            <td>{{ OrderModel.CustomerBillingAttention }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </BaseBlock>
            </div>
            <!-- END Billing Details -->

            <!-- Delivery Details -->
            <div :class="firstRowClass">
                <BaseBlock class="d-flex flex-column  mb-0" title="Delivery">
                    <template #content>
                        <div class="block-rounded block  mb-2">
                            <div class="block-content-full block-content">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 fs-sm">
                                        <tr>
                                            <th>Account</th>
                                            <td>{{ OrderModel.CustomerAccount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ OrderModel.CustomerDeliveryName }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ OrderModel.CustomerDeliveryAddress1 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Zipcode / City</th>
                                            <td>{{ OrderModel.CustomerDeliveryZipcode }}
                                                {{ OrderModel.CustomerDeliveryCity }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Country</th>
                                            <td>{{ OrderModel.CustomerDeliveryCountry }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ OrderModel.CustomerDeliveryEmail }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ OrderModel.CustomerDeliveryPhone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Attention</th>
                                            <td>{{ OrderModel.CustomerDeliveryAttention }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </BaseBlock>
            </div>
            <!-- END Delivery Details -->

            <!-- WebShop User Details -->
            <div v-if="OrderModel.web_shop_user" :class="firstRowClass">
                <BaseBlock class="d-flex flex-column  mb-0" title="WebShop User">
                    <template #content>
                        <div class="block-rounded block  mb-2">
                            <div class="block-content-full block-content">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 fs-sm">
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ OrderModel.web_shop_user.Name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ OrderModel.web_shop_user.Email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Initials</th>
                                            <td>{{ OrderModel.web_shop_user.Initials }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </BaseBlock>
            </div>
            <!-- END WebShop User Details -->
        </div>

        <!-- References Details -->
        <BaseBlock title="References">
            <template #content>
                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table class="table table-striped table-vcenter fs-sm">
                            <thead>
                            <tr>
                                <th>Order No:</th>
                                <th>Order Date:</th>
                                <th>Delivery Date:</th>
                                <th>Order ref:</th>
                                <th>Our ref:</th>
                                <th>Their ref:</th>
                                <th>Employee</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ OrderModel.OrderNumber }}</td>
                                <td v-if="OrderModel.OrderDate !== undefined">
                                    {{ dateFormat(OrderModel.OrderDate, dateFormatStr) }}
                                </td>
                                <td v-if="OrderModel.DeliveryDate !== undefined">
                                    {{ dateFormat(OrderModel.DeliveryDate, dateFormatStr) }}
                                </td>
                                <td>{{ OrderModel.OrderReference }}</td>
                                <td>{{ OrderModel.OurReference }}</td>
                                <td>{{ OrderModel.TheirReference }}</td>
                                <td>{{ OrderModel.Employee }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </BaseBlock>
        <!-- END References Details -->

        <!-- Products Details -->
        <BaseBlock title="Products">
            <template #content>
                <div class="block-content block-content-full">
                    <div class="table-responsive order-line-table">
                        <table class="table table-striped table-vcenter ">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Number</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody class="fs-sm">
                            <tr v-for="order_line of OrderModel.order_lines">
                                <td>
                                    <img v-lazy="order_line.image_urls.ThumbnailSmall"
                                         class="img-responsive"
                                         width="75">
                                </td>
                                <td>{{ order_line.ItemNumber }}</td>
                                <td>{{ order_line.ItemName1 }}</td>
                                <td>{{ order_line.QuantityOrdered }}</td>
                                <td>{{ numberFormat(order_line.Price) }}
                                </td>
                                <td>{{ order_line.DiscountPercent }}</td>
                                <td>
                                    {{ numberFormat(order_line.TotalExVat) }}
                                    {{ OrderModel.CustomerCurrency }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </BaseBlock>
        <!-- END Products Details -->

        <!-- Total Details -->
        <BaseBlock title="Total">
            <template #content>
                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table class="table table-striped table-vcenter fs-sm">
                            <tr>
                                <td><b>Subtotal:</b> {{ numberFormat(OrderModel.TotalExVat) }}
                                    {{ OrderModel.CustomerCurrency }}
                                </td>
                                <td><b>Vat:</b> {{ numberFormat(OrderModel.TotalIncVat - OrderModel.TotalExVat) }}
                                    {{ OrderModel.CustomerCurrency }}
                                </td>
                                <td>
                                    <b>Total:</b> {{ numberFormat(OrderModel.TotalIncVat) }}
                                    {{ OrderModel.CustomerCurrency }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </template>
        </BaseBlock>
        <!-- END Total Details -->

        <!-- Note Details -->
        <BaseBlock title="Note">
            <template #content>
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="fs-sm">
                                {{ OrderModel.Note }}
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </BaseBlock>
        <!-- END Note Details -->
    </div>
</template>

<style scoped>
.order-line-table {
    max-height: 300px;
    overflow: auto;
}

.table thead th {
    text-transform: none !important;
}

</style>
