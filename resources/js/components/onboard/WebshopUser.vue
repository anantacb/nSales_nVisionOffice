<template>
    <h4 class="d-flex justify-content-between">
        User
    </h4>
    <div class="row">
        <div class="col-12">
            <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
            <table v-if="!isLoading && user" class="table">
                <thead class="table-dark">
                <tr>
                    <td>Name</td>
                    <td>Email</td>
                    <td>Login</td>
                    <td>Customer Account</td>
                    <td>Disabled</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ user.Name }}</td>
                    <td>{{ user.Email }}</td>
                    <td>{{ user.Login }}</td>
                    <td>{{ user.AccountNumber }}</td>
                    <td>{{ user.Disabled }}</td>
                </tr>
                </tbody>
            </table>
            <div class="row" v-else>
                <div class="col-6">
                    <h5 class="fw-light">Create User</h5>
                    <form @submit.prevent="createUser">
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Customer<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <VueSelect
                                    v-model="selectedCustomer"
                                    :clearable="false"
                                    :get-option-label="customer => `${customer.Account}: ${customer.Name} - ${customer.Country}, ${customer.Currency}` "
                                    :inputId="`customer-account`"
                                    :loading="false"
                                    :options="customers"
                                    :reduce="role => role.Id"
                                    :searchable="true"
                                    placeholder="Select Customer"
                                    required
                                >
                                </VueSelect>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" class="form-control-sm form-control" autocomplete="off" name="Name" :value="appSubmissionUser.Name" required="" type="text" disabled>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Email<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" class="form-control-sm form-control" autocomplete="off" name="Email" :value="appSubmissionUser.Email" required="" type="text" disabled>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Login<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" class="form-control-sm form-control" autocomplete="off" name="Login" :value="appSubmissionUser.Login" required="" type="text" disabled>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-success" :disabled="!selectedCustomer">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import VueSelect from 'vue-select';
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import Button from "@/components/ui/Button.vue";
import WebShopUser from "@/models/Company/WebShopUser";
import Loader from "@/components/ui/Loader/Loader.vue";
import Customer from "@/models/Company/Customer";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)
const user = ref(null)
const customers = ref([])

const selectedCustomer = ref("")

onMounted(() => {
    getUserDetails();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getUserDetails();
    }
});

const appSubmissionUser = {
    Name: "App Submission",
    Email: "apple@nsales.dk",
    Login: "appsubmission"
}
const getUserDetails = async () => {
    try {
        isLoading.value = true

        let {data} = await WebShopUser.details(companyStore.selectedCompany.Id, "Login", appSubmissionUser.Login);
        user.value = data

        if (user.value) {
            emits("complete", true)
        } else {
            getCustomers()
        }

        isLoading.value = false
    } catch (err) {

        isLoading.value = false
    }
}

async function getCustomers() {
    try {
        const request = {
            filters: [
                {
                    column: "Blocked",
                    operator: "=",
                    values: "0"
                }
            ],
            pagination: {
                page_no: 1,
                per_page: 20
            }
        }
        let {data, pagination} = await Customer.getCustomers(companyStore.selectedCompany.Id, request);
        customers.value = data
    } catch (err) {

    }
}

async function createUser() {
    try {
        isLoading.value = true

        const request = {...appSubmissionUser}

        const customer = customers.value.find(customer => customer.Id === selectedCustomer.value)
        request.AccountNumber = customer.Account

        let {data} = await WebShopUser.createTestUser(companyStore.selectedCompany.Id, request);
        user.value = data

        if (user.value) {
            emits("complete", true)
        }

        isLoading.value = false
    } catch (err) {
        isLoading.value = false
    }
}
</script>
