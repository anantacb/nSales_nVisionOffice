import {defineStore} from "pinia";
import Company from "@/models/Office/Company";
import Module from "@/models/Office/Module";
import {useAuthStore} from "@/stores/authStore";

export const useCompanyStore = defineStore('company', {
    state: () => ({
        companies: [],
        selectedCompany: localStorage.getItem('selectedCompany') ? JSON.parse(localStorage.getItem('selectedCompany')) : {},
        selectedCompanyModules: [],
    }),
    actions: {
        async setSelectedCompanyById(companyId) {
            let tempCompany = this.companies.filter((company) => {
                return company.Id === parseInt(companyId);
            })[0];

            this.selectedCompany = tempCompany;

            localStorage.setItem('selectedCompany', JSON.stringify(this.selectedCompany));

            const authStore = useAuthStore();
            authStore.setRoles(this.selectedCompany.roles);

            let {data} = await Module.getActivatedModulesByCompany(tempCompany.Id);
            this.selectedCompanyModules = data;
            localStorage.setItem('selectedCompanyModules', JSON.stringify(data));
        },

        async fill() {
            //let {data} = await Company.getAllCompanies();
            let {data} = await Company.getAuthUserCompanies();
            this.companies = data;
            if (localStorage.getItem('selectedCompany')) {
                await this.setSelectedCompanyById(JSON.parse(localStorage.getItem('selectedCompany')).Id);
            } else {
                await this.setSelectedCompanyById(this.companies[0].Id);
            }
        },

        clearStorage() {
            this.companies = [];
            this.selectedCompany = {};
            this.selectedCompanyModules = [];
        }
    },
    getters: {
        getCompaniesForDropDownOptions() {
            return this.companies.map((company) => {
                return {
                    label: company.Name,
                    value: company.Id
                }
            });
        },

        getSelectedCompany() {
            return this.selectedCompany;
        },

        getSelectedCompanyModuleNames() {
            return this.selectedCompanyModules.map((module) => {
                return module.Name;
            });
        }
    }
});
