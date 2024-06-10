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
import Swal from "sweetalert2";

const route = useRoute();
const authStore = useAuthStore();
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {numberFormat, dateFormat} = useFormatter();
const {isModuleEnabled} = useCompanyInfos();
const {errors, setErrors, resetErrors} = useFormErrors();
const emit = defineEmits(['setProductInfo']);

let TabAttributesRef = ref(null);
let ItemAttributes = ref([]);
let WebShopLanguages = ref([]);
let WebShopLanguageOptions = ref([]);

async function getProductItemAttributes() {
    let {data} = await ItemAttribute.fetchByItem(companyStore.selectedCompany.Id, route.params.id);
    ItemAttributes.value = data;
}

async function getAllWebShopLanguages() {
    let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
    WebShopLanguages.value = data;

    let options = [{label: 'Select Language', value: ''}];

    data.forEach((language) => {
        options.push({label: language.Name, value: language.Code});
    });

    WebShopLanguageOptions.value = options;
}

function addItemAttribute() {
    ItemAttributes.value.push({
        Id: "",
        TypeCode: "",
        Language: "",
        Value: ""
    });
}

function removeItemAttribute(itemAttribute, index) {        // Need to be added logic

    Swal.fire({
        title: 'Are you sure? Delete Attribute?',
        html: 'Please type <code class="text-danger">Confirm</code> and press delete.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            // let {data, message} = await Order.delete(module.Id);
            // tableData.value.splice(index, 1);
            ItemAttributes.value.splice(index, 1);
            notificationStore.showNotification('message');
        }
    });

}
function updateItemAttributes() {
    alert('Under dev');
}

onMounted(async () => {

    console.log(_.isEmpty(ItemAttributes.value));
    if (_.isEmpty(ItemAttributes.value)) {
        console.log('attributes');

        TabAttributesRef.value.statusLoading();
        // await getAllWebShopLanguages();
        // await getProductItemAttributes();

        isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguages.value = [];
        isModuleEnabled('Itemattribute') ? await getProductItemAttributes() : ItemAttributes.value = [];

        TabAttributesRef.value.statusNormal();
    }

});
</script>

<template>
    <BaseBlock ref="TabAttributesRef" content-full>
        <!--        <h4 class="fw-normal">Attributes Content</h4>-->
        <!--        <p>...</p>-->

        <div class="table-responsive">
            <!--            <button v-if="userHasRole(`Administrator`) || userHasRole(`Developer`)"-->
            <!--                    class="btn btn-outline-info btn-sm float-right mb-2"-->
            <!--                    @click="exportItemAttributes">-->
            <!--                <i class="fa fa-arrow-circle-down"></i>-->
            <!--                Export Attributes-->
            <!--            </button>-->
            <table class="table table-striped fs-sm">
                <thead>
                <tr>
                    <th>Attribute</th>
                    <th>Value</th>
                    <th>Language</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(ItemAttribute,index) in ItemAttributes">
                    <td>
                        <input v-model="ItemAttribute.TypeCode" class="form-control form-control-sm"
                               type="text">

                        <!--                        <div v-if="errors.item_attributes[`item_attributes.${index}.TypeCode`]">-->
                        <!--                            <small-->
                        <!--                                v-for="error in errors.item_attributes[`item_attributes.${index}.TypeCode`]"-->
                        <!--                                class="form-text text-danger"-->
                        <!--                                v-text="error">-->
                        <!--                            </small>-->
                        <!--                        </div>-->

                    </td>
                    <td>
                        <input v-model="ItemAttribute.Value" class="form-control form-control-sm" type="text">
                    </td>

                    <td>
                        <Select
                            :modelValue="ItemAttribute.Language"
                            :options="WebShopLanguageOptions"
                            :required="true"
                            name="Language"
                            select-class="form-control form-select-sm"
                        />
                        <!--                                @change="companyChanged"-->
                        <!--                        <select v-model="ItemAttribute.Language" class="form-control custom-select">-->
                        <!--                            <option value="">Select Language</option>-->
                        <!--                            <option v-for="language in WebShopLanguages"-->
                        <!--                                    :value="language.Code">{{ language.Name }}-->
                        <!--                            </option>-->
                        <!--                        </select>-->

                    </td>

                    <td>
                        <button class="btn btn-danger btn-sm"
                                @click="removeItemAttribute(ItemAttribute,index)"><i
                            class="fa fa-trash"></i></button>
                    </td>

                </tr>

                </tbody>
            </table>
        </div>

        <button class="btn btn-outline-success btn-sm float-end mb-2"
                type="submit"
                @click="addItemAttribute"
        >
            Add More Attribute
        </button>

        <button class="btn btn-outline-primary btn-sm col-2"
                type="submit"
                @click="updateItemAttributes"
        >

            Update Attributes
        </button>


    </BaseBlock>
</template>

<style scoped>
</style>
