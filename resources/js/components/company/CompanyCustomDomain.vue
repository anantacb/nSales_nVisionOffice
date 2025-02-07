<script setup>
import {onMounted, ref} from "vue";
import Company from "@/models/Office/Company";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Deploy from "@/models/Company/Deploy";
import _ from "lodash";
import Swal from "sweetalert2";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const props = defineProps(["CompanyId"]);

const updateCustomDomainRef = ref(null);
const newCompanyCustomDomain = ref(null);
const CompanyCustomDomains = ref({});
const CompanyDeploymentStatus = ref({});

async function getCompanyCustomDomains() {
    try {
        let {data} = await Company.getCompanyCustomDomains(props.CompanyId);
        CompanyCustomDomains.value = data;
    } catch (error) {
        console.log(error);
        updateCustomDomainRef.value.statusNormal();
    }
}

async function getCompanyDeploymentStatus() {
    try {
        let {data} = await Deploy.getCompanyDeploymentStatus(props.CompanyId);
        CompanyDeploymentStatus.value = data;
    } catch (error) {
        console.log(error);
        updateCustomDomainRef.value.statusNormal();
    }
}

async function addCompanyDomain() {
    updateCustomDomainRef.value.statusLoading();

    let formData = {
        'CustomDomain': newCompanyCustomDomain.value,
        'UUID': CompanyDeploymentStatus.value.prod.uuid,
    };

    try {
        let {data, message} = await Company.addCompanyCustomDomain(props.CompanyId, formData);
        CompanyCustomDomains.value = data;
        notificationStore.showNotification(message);
    } catch (error) {
        console.log(error);
        if (error.response && error.response.status === 422) {
            setErrors(error.response.data.errors);
        }
    } finally {
        updateCustomDomainRef.value.statusNormal();
    }

}

function getProdHostId(domain) {
    let hosts = (CompanyDeploymentStatus.value.prod && CompanyDeploymentStatus.value.prod.hosts) ? CompanyDeploymentStatus.value.prod.hosts : {};
    let matchingHost = hosts.find(host => host.hostname === domain);
    return matchingHost ? matchingHost.id : undefined;
}

async function removeCompanyDomain(companyDomain, index) {
    Swal.fire({
        title: 'Are you sure? Delete Custom Domain?',
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
            let formData = {
                CustomDomain: companyDomain,
                HostId: getProdHostId(companyDomain),
            }

            updateCustomDomainRef.value.statusLoading();
            try {
                let {data, message} = await Company.deleteCompanyCustomDomain(props.CompanyId, formData);
                // console.log(data);
                // CompanyCustomDomains.value.splice(index, 1);
                CompanyCustomDomains.value = data;
                notificationStore.showNotification(message);
            } catch (error) {
                console.log(error);
                if (error.response && error.response.status === 422) {
                    setErrors(error.response.data.errors);
                }
                if (error.response && error.response.status === 404) {
                    notificationStore.showNotification(error.response.data.message, 'error');
                }
            }
            updateCustomDomainRef.value.statusNormal();
        }
    });
}

onMounted(async () => {
    updateCustomDomainRef.value.statusLoading();
    await getCompanyCustomDomains();
    await getCompanyDeploymentStatus();
    updateCustomDomainRef.value.statusNormal();
});

</script>

<template>
    <BaseBlock ref="updateCustomDomainRef" content-full title="Company Domain">

        <form v-if="CompanyDeploymentStatus && CompanyDeploymentStatus.prod" class="space-y-4"
              @submit.prevent="addCompanyDomain">

            <div class="row pb-4">
                <label class="col-sm-4 col-form-label col-form-label-sm"
                       for="NewCompanyCustomDomains">
                    Customer Domain
                    <span class="text-danger">*</span>
                </label>

                <div class="col-sm-6">
                    <input
                        v-model="newCompanyCustomDomain"
                        :class="errors.CustomDomain ? `is-invalid form-control-sm` : `form-control-sm`"
                        autocomplete="off"
                        class="form-control form-control-sm"
                        name="newCompanyCustomDomain"
                        placeholder="add new domain"
                        type="text"
                        @keyup="resetErrors"/>

                    <InputErrorMessages v-if="errors.CustomDomain"
                                        :errorMessages="errors.CustomDomain"></InputErrorMessages>
                </div>

                <div class="col-sm-2">
                    <button class="btn btn-outline-primary btn-sm" type="submit">Add</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="table-responsive fs-sm">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th colspan="2">Custom Domain List</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr v-if="_.isEmpty(CompanyCustomDomains)">
                        <td colspan="3">
                            There is no custom domain
                        </td>
                    </tr>

                    <tr v-for="(companyCustomDomain,index) in CompanyCustomDomains" v-else>
                        <td>
                            {{ ++index }}
                        </td>
                        <td>
                            {{ companyCustomDomain }}
                        </td>

                        <td v-if="CompanyDeploymentStatus && CompanyDeploymentStatus.prod">
                            <button class="btn btn-danger btn-sm"
                                    @click="removeCompanyDomain(companyCustomDomain,index)"><i
                                class="fa fa-trash"></i></button>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </BaseBlock>
</template>
