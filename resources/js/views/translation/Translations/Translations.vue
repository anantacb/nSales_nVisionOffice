<script setup>
import TranslationList from "@/views/translation/Translations/TranslationList.vue";
import Translation from "@/models/Office/Translation";
import {useTemplateStore} from "@/stores/templateStore";
import {useNotificationStore} from "@/stores/notificationStore";

const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

async function syncTranslations() {
    templateStore.pageLoader({mode: "on"});
    let {data, message} = await Translation.syncTranslations();
    notificationStore.showNotification(message);
    templateStore.pageLoader({mode: 'off'});
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Translations">
            <template #options>
                <button class="btn btn-sm btn-outline-info" @click="syncTranslations">
                    <i class="fa fa-sync"></i> Sync
                </button>
            </template>
            <TranslationList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
