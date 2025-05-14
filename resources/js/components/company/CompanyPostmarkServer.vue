<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Button from "@/components/ui/Button.vue";
import Swal from "sweetalert2";
import Company from "@/models/Office/Company";
import _ from 'lodash';

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const props = defineProps(["CompanyId"]);
const postmarkServerRef = ref(null);

let PostmarkServer = ref(null);

async function createPostmarkServer() {
    Swal.fire({
        title: 'Are you sure? Create Postmark Server?',
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
            postmarkServerRef.value.statusLoading();
            let formData = {
                'CompanyId': props.CompanyId,
            };
            let {data, message} = await Company.createPostmarkServer(formData);
            PostmarkServer.value = data;
            postmarkServerRef.value.statusNormal();
            notificationStore.showNotification(message);
        }
    });
}

async function getPostmarkServer() {
    postmarkServerRef.value.statusLoading();
    let formData = {
        'CompanyId': props.CompanyId,
    };
    let {data, message} = await Company.getPostmarkServer(formData);
    PostmarkServer.value = data;
    postmarkServerRef.value.statusNormal();
}

onMounted(async () => {
    await getPostmarkServer();
});
</script>

<template>
    <BaseBlock ref="postmarkServerRef" content-full title="Postmark Email Server">
        <template v-if="!_.isEmpty(PostmarkServer)">
            <a :href="PostmarkServer.ServerLink" target="_blank">
                {{ PostmarkServer.ServerName }} ({{ PostmarkServer.ServerId }})
            </a>
        </template>
        <template v-else>
            <button class="btn btn-primary" @click="createPostmarkServer">Create Server</button>
        </template>
    </BaseBlock>
</template>
