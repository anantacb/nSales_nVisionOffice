<template>
    <div class="card position-relative">
        <img :src="getPreviewImage(theme.PreviewImageUrl)[0]" class="card-img-top" alt="...">
        <div class="card-body">
            <p class="card-text">
                <a :href="theme.PreviewUrl" target="_blank">{{ theme.Name }}</a>
                <br v-if="theme.parent">
                <small v-if="theme.parent">
                    Child theme of <b>{{ theme.parent.Name }}</b>
                </small>
                <br v-if="theme.CompanyId">
                <div v-if="theme.CompanyId">
                    <span class="text-success">Available for</span>
                    <br>
                    <small class="badge bg-secondary me-1" v-for="company in getThemeAvailableForCompanies(theme.CompanyId)">{{ company.Name }}</small>
                </div>
            </p>
        </div>

        <button class="btn btn-warning m-2" :disabled="deploying" @click.prevent="triggerBuild(theme.Id)">Deploy all</button>


        <div v-if="theme.parent" class="badge bg-info position-absolute" style="top: 10px; left: 10px;">Child</div>
        <div v-if="theme.company_theme_count > 0" class="badge bg-success position-absolute" style="top: 10px; right: 10px;">
            {{ theme.company_theme_count }} company using this theme
        </div>
    </div>
</template>
<script setup>
import {useCompanyStore} from "@/stores/companyStore";
import {ref} from "vue";
import Theme from "@/models/Office/Theme";
import {useNotificationStore} from "@/stores/notificationStore";

const props = defineProps(['theme'])

const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();


const deploying = ref(false)

const getPreviewImage = (images) => {
    return images.split(",")
}

const getThemeAvailableForCompanies = (companyIdString) => {
    const companyIds = companyIdString.split(",").map(value => +value)
    return companyStore.companies.filter(company => companyIds.indexOf(company.Id) > -1)
}

const triggerBuild = async (themeId) => {
    try {
        deploying.value = true
        const { message } = await Theme.triggerBuild(themeId)
        deploying.value = false

        notificationStore.showNotification(message);
    } catch (err) {
        deploying.value = false
    }
}
</script>
