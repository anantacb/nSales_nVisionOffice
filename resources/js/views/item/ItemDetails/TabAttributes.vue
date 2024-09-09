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

const props = defineProps({
    productInfo: {
        type: Object,
        required: true,
    },
    webShopLanguages: {
        type: Array,
        required: true,
    },
});

async function getAllWebShopLanguages() {
    // let {data} = await WebShopLanguage.fetchAll(companyStore.selectedCompany.Id, route.params.id);
    // WebShopLanguages.value = data;

    let options = [{label: 'Select Language', value: ''}];
    props.webShopLanguages.forEach((language) => {
        options.push({label: language.Name, value: language.Code});
    });
    WebShopLanguageOptions.value = options;
}

async function getItemAttributes() {
    let {data} = await ItemAttribute.fetchByItem(companyStore.selectedCompany.Id, route.params.id);
    ItemAttributes.value = data;
}

function addItemAttribute() {
    ItemAttributes.value.push({
        TypeCode: "",
        Language: "",
        Value: ""
    });
}

async function updateItemAttributes() {

    if (_.isEmpty(ItemAttributes.value)) {
        notificationStore.showNotification("Add at least one Attribute.", 'error');
        return;
    }
    TabAttributesRef.value.statusLoading();

    try {
        let {data, message} = await ItemAttribute.updateItemAttributes(
            companyStore.selectedCompany.Id, route.params.id,
            props.productInfo.Number, ItemAttributes.value
        );
        ItemAttributes.value = data;
        console.log(ItemAttributes.value);
        notificationStore.showNotification(message);
        TabAttributesRef.value.statusNormal();
    } catch (error) {
        console.log(error);
        setErrors(error.response.data.errors);
        TabAttributesRef.value.statusNormal();
    }
}

function removeItemAttribute(itemAttribute, index) {

    if (!itemAttribute.Id) {
        ItemAttributes.value.splice(index, 1);
        notificationStore.showNotification('Itemattribute deleted successfully.');
        return;
    }

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
            TabAttributesRef.value.statusLoading();
            let {data, message} = await ItemAttribute.delete(companyStore.selectedCompany.Id, itemAttribute.Id);
            ItemAttributes.value.splice(index, 1);
            notificationStore.showNotification(message);
            TabAttributesRef.value.statusNormal();
        }
    });

}

onMounted(async () => {

    if (_.isEmpty(ItemAttributes.value)) {
        TabAttributesRef.value.statusLoading();
        isModuleEnabled('WSLanguage') ? await getAllWebShopLanguages() : WebShopLanguageOptions.value = [];
        isModuleEnabled('Itemattribute') ? await getItemAttributes() : ItemAttributes.value = [];
        TabAttributesRef.value.statusNormal();
    }

});
</script>

<template>
    <BaseBlock ref="TabAttributesRef" content-full>
        <div class="table-responsive">
            <!-- Need to be add Export Attribute -->
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
                        <input v-model="ItemAttribute.TypeCode"
                               :class="{'is-invalid' : errors[`ItemAttributes.${index}.TypeCode`]}"
                               class="form-control form-control-sm"
                               type="text" @keyup="resetErrors">

                        <InputErrorMessages
                            v-if="errors[`ItemAttributes.${index}.TypeCode`]"
                            :errorMessages="errors[`ItemAttributes.${index}.TypeCode`]">
                        </InputErrorMessages>

                    </td>
                    <td>
                        <input v-model="ItemAttribute.Value"
                               :class="{'is-invalid' : errors[`ItemAttributes.${index}.Value`]}"
                               class="form-control form-control-sm"
                               type="text"
                               @keyup="resetErrors">

                        <InputErrorMessages
                            v-if="errors[`ItemAttributes.${index}.Value`]"
                            :errorMessages="errors[`ItemAttributes.${index}.Value`]">
                        </InputErrorMessages>
                    </td>

                    <td>
                        <Select
                            v-model="ItemAttribute.Language"
                            :options="WebShopLanguageOptions"
                            :required="true"
                            :select-class="errors[`ItemAttributes.${index}.Language`]
                            ? `is-invalid form-control form-select-sm` : `form-control form-select-sm`"
                            name="Language"
                            @change="resetErrors"
                        />

                        <InputErrorMessages
                            v-if="errors[`ItemAttributes.${index}.Language`]"
                            :errorMessages="errors[`ItemAttributes.${index}.Language`]">
                        </InputErrorMessages>

                    </td>

                    <td>
                        <button class="btn btn-danger btn-sm" @click="removeItemAttribute(ItemAttribute,index)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>

                </tr>

                </tbody>
            </table>
        </div>

        <button class="btn btn-outline-success btn-sm float-end mb-2" type="submit" @click="addItemAttribute">
            Add More Attribute
        </button>

        <button class="btn btn-outline-primary btn-sm col-2" type="submit" @click="updateItemAttributes">
            Update Attributes
        </button>

    </BaseBlock>
</template>

<style scoped>
</style>
