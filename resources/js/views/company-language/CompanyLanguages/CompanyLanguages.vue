<script setup>
import CompanyLanguageList from "@/views/company-language/CompanyLanguages/CompanyLanguageList.vue";
import {onMounted, ref, watch} from "vue";
import useCompanyInfos from "@/composables/useCompanyInfos";
import {useNotificationStore} from "@/stores/notificationStore";
import router from "@/router";
import {useCompanyStore} from "@/stores/companyStore";
import ModalComponent from "@/components/ui/Modal/Modal.vue";
import Language from "@/models/Office/Language";
import {useFormErrors} from "@/composables/useFormErrors";
import CompanyLanguage from "@/models/Company/CompanyLanguage";

let {isModuleEnabled} = useCompanyInfos();
const notificationStore = useNotificationStore();
const companyStore = useCompanyStore();

let {errors, setErrors, resetErrors} = useFormErrors();

const companyLanguageListRef = ref(null);
const modal = ref(null);

function openAddLanguageModal() {
    modal.value.openModal();
}

async function addLanguage() {
    if (!LanguageId.value) {
        notificationStore.showNotification('Please select Language.', 'error');
        return;
    }
    let {data, message} = await CompanyLanguage.addCompanyLanguage(companyStore.selectedCompany.Id, LanguageId.value);
    modal.value.closeModal();
    notificationStore.showNotification(message);
    companyLanguageListRef.value.refresh();
}

let LanguageId = ref(null);
let LanguageOptions = ref([]);

async function getAllLanguages() {
    const {data} = await Language.getAllLanguages();
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    LanguageOptions.value = options;
}

onMounted(async () => {
    await getAllLanguages();
});

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
        <BaseBlock title="Languages">
            <template #options>
                <button class="btn btn-sm btn-outline-primary me-1" type="button" @click="openAddLanguageModal">Add
                    Language
                </button>
            </template>
            <CompanyLanguageList ref="companyLanguageListRef"/>
        </BaseBlock>

        <ModalComponent id="queries" ref="modal" modal-body-classes="modal-dialog-centered">
            <template v-slot:modal-content>
                <BaseBlock class="mb-0" title="Add Language" transparent>
                    <template #content>
                        <div class="block-content fs-sm scrollable-modal-content mb-2">
                            <div class="row">
                                <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                    Language<span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <Select id="Language" v-model="LanguageId" :options="LanguageOptions"
                                            :required="true"
                                            :select-class="errors.LanguageId ? `is-invalid form-select-sm` : `form-select-sm`"
                                            name="Language"
                                    />
                                    <InputErrorMessages v-if="errors.LanguageId"
                                                        :errorMessages="errors.LanguageId"></InputErrorMessages>
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button class="btn btn-sm btn-outline-danger me-1" data-bs-dismiss="modal" type="button">
                                Cancel
                            </button>
                            <button class="btn btn-sm btn-outline-success" type="button" @click="addLanguage">
                                Save
                            </button>
                        </div>
                    </template>
                </BaseBlock>
            </template>
        </ModalComponent>
    </div>
    <!-- END Page Content -->
</template>
