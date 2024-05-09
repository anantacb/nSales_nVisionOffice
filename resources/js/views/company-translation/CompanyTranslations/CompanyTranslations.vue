<script setup>
import CompanyTranslationList from "@/views/company-translation/CompanyTranslations/CompanyTranslationList.vue";
import {watch} from "vue";
import router from "@/router";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useCompanyStore} from "@/stores/companyStore";
import {useNotificationStore} from "@/stores/notificationStore";

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();

let {isModuleEnabled} = useCompanyInfos();
watch(() => companyStore.selectedCompanyModules, () => {
    if (!isModuleEnabled('Translation')) {
        router.push({name: 'home'});
        notificationStore.showNotification('Module Not Enabled.', 'error', 15000);
    }
});
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Translations">
            <CompanyTranslationList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
