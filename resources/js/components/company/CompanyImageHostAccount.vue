<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import Swal from "sweetalert2";
import Company from "@/models/Office/Company";
import _ from 'lodash';

const notificationStore = useNotificationStore();

const props = defineProps(["CompanyId"]);
const imageHostAccountRef = ref(null);

let ImageHostAccount = ref(null);

async function createImageHostAccount() {
    Swal.fire({
        title: 'Are you sure? Create Bunny Image Host Account?',
        html: 'Please type <code class="text-danger">Confirm</code> and press confirm.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        confirmButtonColor: 'green',
        cancelButtonText: 'Cancel',
        cancelButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            imageHostAccountRef.value.statusLoading();
            let formData = {
                'CompanyId': props.CompanyId,
            };
            let {data, message} = await Company.createImageHostAccount(formData);
            ImageHostAccount.value = data;
            imageHostAccountRef.value.statusNormal();
            notificationStore.showNotification(message);
        }
    });
}

async function getImageHostAccount() {
    imageHostAccountRef.value.statusLoading();
    let formData = {
        'CompanyId': props.CompanyId,
    };
    let {data} = await Company.getImageHostAccount(formData);
    ImageHostAccount.value = data;
    imageHostAccountRef.value.statusNormal();
}

onMounted(async () => {
    await getImageHostAccount();
});
</script>

<template>
    <BaseBlock ref="imageHostAccountRef" content-full title="Bunny Image Host Account">
        <template v-if="!_.isEmpty(ImageHostAccount)">
            <div><strong>Zone Name:</strong> {{ ImageHostAccount.FTPUserName }}</div>
            <div><strong>CDN URL:</strong> {{ ImageHostAccount.Home }}</div>
            <div><strong>FTP Domain:</strong> {{ ImageHostAccount.FTPDomainName }}</div>
            <div><strong>Root Path:</strong> {{ ImageHostAccount.FTPRootPath }}</div>
        </template>
        <template v-else>
            <button class="btn btn-primary" @click="createImageHostAccount">Create Image Host Account</button>
        </template>
    </BaseBlock>
</template>
