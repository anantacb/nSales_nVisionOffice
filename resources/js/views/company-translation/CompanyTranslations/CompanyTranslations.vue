<script setup>
import CompanyTranslationList from "@/views/company-translation/CompanyTranslations/CompanyTranslationList.vue";
import {watch} from "vue";
import router from "@/router";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";
import CompanyTranslation from "@/models/Company/CompanyTranslation";
import {useTemplateStore} from "@/stores/templateStore";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
const templateStore = useTemplateStore();

let {isModuleEnabled} = useCompanyInfos();
watch(() => companyStore.selectedCompanyModules, () => {
    if (!isModuleEnabled('Translation')) {
        router.push({name: 'home'});
        notificationStore.showNotification('Module Not Enabled.', 'error', 15000);
    }
});

async function syncCompanyTranslations() {
    templateStore.pageLoader({mode: "on"});
    let {data, message} = await CompanyTranslation.syncCompanyTranslations(companyStore.selectedCompany.Id);
    notificationStore.showNotification(message);
    templateStore.pageLoader({mode: 'off'});
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Translations">
            <template #options>
                <button class="btn btn-sm btn-outline-info" @click="syncCompanyTranslations">
                    <i class="fa fa-sync"></i> Sync
                </button>
            </template>
            <CompanyTranslationList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
