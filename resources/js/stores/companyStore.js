import {defineStore} from "pinia";
import Company from "@/models/Office/Company";

export const useCompanyStore = defineStore('company', {
    state: () => ({
        companies: [],
        selectedCompany: localStorage.getItem('selectedCompany') ? JSON.parse(localStorage.getItem('selectedCompany')) : {}
    }),
    actions: {
        setSelectedCompanyById(companyId) {
            this.selectedCompany = this.companies.filter((company) => {
                return company.Id === parseInt(companyId);
            })[0];
            localStorage.setItem('selectedCompany', JSON.stringify(this.selectedCompany));
        },

        async fill() {
            let {data} = await Company.getAllCompanies();
            this.companies = data;
            if (localStorage.getItem('selectedCompany')) {
                this.setSelectedCompanyById(JSON.parse(localStorage.getItem('selectedCompany')).Id);
            } else {
                this.setSelectedCompanyById(this.companies[0].Id);
            }
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
        }
    }
});
