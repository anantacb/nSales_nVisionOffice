<script setup>
import CopyDatabaseForm from "@/views/database/CopyDatabase/CopyDatabaseForm.vue";
import {ref} from "vue";
import Table from "@/models/Office/Table";
import {useNotificationStore} from "@/stores/notificationStore";
import router from "@/router";

let sqlPreviewData = ref([]);
let sqlFormData = ref({});
let modal = ref(null);

const notificationStore = useNotificationStore();

async function saveWithoutExecuting() {
    try {
        let {message} = await Table.createTableSaveWithoutExecuting(sqlFormData.value);
        modal.value.closeModal();
        await router.push({name: 'tables'});
        notificationStore.showNotification(message);
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error', 15000);
        modal.value.closeModal();
    }
}

async function saveAndExecute() {
    try {
        let {message} = await Table.createTableSaveAndExecute(sqlFormData.value);
        modal.value.closeModal();
        await router.push({name: 'tables'});
        notificationStore.showNotification(message);
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error', 15000);
        modal.value.closeModal();
    }
}

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock content-full title="Copy DB To Dev">
            <template #options>
                <router-link :to="{name:'tables'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <CopyDatabaseForm/>
        </BaseBlock>

    </div>
    <!-- END Page Content -->
</template>
