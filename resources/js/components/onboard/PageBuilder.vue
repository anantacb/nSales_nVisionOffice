<template>
    <div class="position-relative max-h-80vh h-100 overflow-auto">
        <h4 class="d-flex justify-content-between">
            {{ step.Title }}
            <div>
                <button class="btn btn-sm btn-outline-success"
                        v-if="!isLoading && missingRequiredPages.length > 0"
                        @click.prevent="createPages"
                >
                    Create Missing Required Pages
                </button>
                <button class="btn btn-sm btn-outline-success ms-2"
                        v-if="!isLoading && Object.keys(missingRequiredPagesContent).length > 0"
                        @click.prevent="createPagesContentForMissingLanguages"
                >
                    Create Missed Pages Content
                </button>
            </div>
        </h4>
        <div class="row">
            <div class="col-12">
                <Loader v-if="isLoading" :is-loading="isLoading"></Loader>
                <table v-else-if="!isLoading" class="table">
                    <thead class="table-dark">
                    <tr>
                        <td>Name</td>
                        <td>SystemKey</td>
                        <td>Required</td>
                        <td>Disabled</td>
                        <td>Languages</td>
                        <td>Action</td>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-if="pages && pages.length > 0">
                        <tr v-for="page in pages">
                            <td>{{ page.Name }}</td>
                            <td>{{ page.SystemKey }}</td>
                            <td>{{ !!step.payload.requiredPages[page.SystemKey] }}</td>
                            <td>{{ page.Disabled }}</td>
                            <td v-if="languages && languages.length > 0">
                                <span class="badge me-1" :class="{'bg-success': active, 'bg-danger': !active}"
                                      v-for="(active, lang) in getAvailablePageLanguages(page.web_shop_text)">{{
                                        lang
                                    }}</span>
                            </td>
                            <td></td>
                        </tr>
                        <tr v-for="page in missingRequiredPages">
                            <td>{{ step.payload.requiredPages[page]['name'] }}</td>
                            <td>{{ page }}</td>
                            <td>true</td>
                            <td></td>
                            <td></td>
                            <td>
                                <span class="badge bg-danger">Not Available</span>
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td colspan="4">No pages found!!!</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script setup>
import {computed, defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import WebShopPage from "@/models/Company/WebShopPage";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import Loader from "@/components/ui/Loader/Loader.vue";
import Button from "@/components/ui/Button.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const isLoading = ref(false)

onMounted(() => {
    getPageList();
    getCompanyLanguages();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getPageList();
        getCompanyLanguages();
    }
});

const pages = ref([])

async function getPageList() {
    try {
        isLoading.value = true

        const platform = props.step.payload && props.step.payload.platform ? props.step.payload.platform : "web"
        const {data} = await WebShopPage.list(companyStore.selectedCompany.Id, platform)
        pages.value = data

        isLoading.value = false
    } catch (err) {
        isLoading.value = false
    }
}

const languages = ref([])

async function getCompanyLanguages() {
    try {
        let {data, pagination} = await CompanyLanguage.getCompanyLanguages(companyStore.selectedCompany.Id, {
            pagination: {"page_no": 1, "per_page": 20}
        });
        languages.value = data

    } catch (err) {

    }
}

function getAvailablePageLanguages(webShopTexts) {
    const languageAndPageMap = {}
    for (let language of languages.value) {
        const text = webShopTexts.find(text => text.Language === language.Code && text.Type === "Body")
        if (text) {
            languageAndPageMap[language.Code] = 1
        } else {
            languageAndPageMap[language.Code] = 0
        }
    }

    return languageAndPageMap
}

const missingRequiredPages = computed(() => {
    const requiredPages = new Set(Object.keys(props.step.payload.requiredPages))
    const availablePages = new Set(pages.value && pages.value.length > 0 ? pages.value.map(page => page.SystemKey) : [])

    return Array.from(requiredPages.difference(availablePages))
})

const missingRequiredPagesContent = computed(() => {
    const requiredPages = Object.keys(props.step.payload.requiredPages)
    const availableRequiredPages = pages.value && pages.value.length > 0 ? pages.value.filter(page => requiredPages.indexOf(page.SystemKey) >= 0) : []

    const missedContent = {}
    if (availableRequiredPages.length && languages.value.length) {
        for (let page of availableRequiredPages) {
            const temp = []
            for (let language of languages.value) {
                const text = page.web_shop_text.find(text => text.Language === language.Code && text.Type === "Body")
                if (!text) {
                    temp.push(language.Code)
                }
            }

            if (temp.length) {
                missedContent[page.Id] = temp
            }
        }
    }

    return missedContent
})

watch([missingRequiredPages, missingRequiredPagesContent, isLoading], ([newMissingRequiredPages, newMissingRequiredPagesContent]) => {
    if (!isLoading.value
        && !missingRequiredPages.value.length
        && !Object.keys(missingRequiredPagesContent.value).length
    ) {
        emits("complete", true)
    }
})

async function createPages() {
    if(missingRequiredPages.value && missingRequiredPages.value.length > 0) {
        const pages = [];
        const companyLanguages = languages.value && languages.value.length?  languages.value.map(language => language.Code) : []

        for (let systemKey of missingRequiredPages.value) {
            pages.push({
                "SystemKey": systemKey,
                "Name": props.step.payload.requiredPages[systemKey]['name'],
                "Platform": props.step.payload.platform,
                "languages": companyLanguages
            })
        }

        const data = await WebShopPage.createPagesC(companyStore.selectedCompany.Id, pages)
        if (data.success) {
            getPageList()
        }
    }
}

async function createPagesContentForMissingLanguages() {
    if (missingRequiredPagesContent.value && Object.keys(missingRequiredPagesContent.value).length > 0) {
        const data = await WebShopPage.createPagesContentForMissingLanguages(companyStore.selectedCompany.Id, missingRequiredPagesContent.value);
        if (data.success) {
            getPageList()
        }
    }
}
</script>
