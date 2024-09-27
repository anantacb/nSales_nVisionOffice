import {ref} from "vue";
import CompanyLanguage from "@/models/Company/CompanyLanguage";
import {useCompanyStore} from "@/stores/companyStore";

export function useCompanyLanguage() {

    const companyStore = useCompanyStore();
    let CompanyLanguages = ref([]);
    let CompanyLanguageOptions = ref([]);

    async function getAllCompanyLanguages() {
        let {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id);
        CompanyLanguages.value = data;
    }

    async function getCompanyLanguageOptions(companyLanguages) {
        let {data} = await CompanyLanguage.getAllCompanyLanguages(companyStore.selectedCompany.Id);

        let options = [{label: 'Select Language', value: ''}];
        data.forEach((language) => {
            options.push({label: language.Name, value: language.Code});
        });
        CompanyLanguageOptions.value = options;
    }

    async function getLanguageOptionsByCompanyLanguage(companyLanguages) {
        let options = [{label: 'Select Language', value: ''}];
        companyLanguages.forEach((language) => {
            options.push({label: language.Name, value: language.Code});
        });
        CompanyLanguageOptions.value = options;
    }

    return {
        CompanyLanguages,
        CompanyLanguageOptions,
        getAllCompanyLanguages,
        getCompanyLanguageOptions,
        getLanguageOptionsByCompanyLanguage
    };
}
