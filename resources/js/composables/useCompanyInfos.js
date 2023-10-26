import {useCompanyStore} from "@/stores/companyStore";

export default function useCompanyInfos() {
    function isModuleEnabled(module) {
        const companyStore = useCompanyStore();
        return companyStore.getSelectedCompanyModuleNames.includes(module);
    }

    return {isModuleEnabled};
}
