import {defineStore} from "pinia";
import Company from "@/models/Office/Company";
import _ from "lodash";

export const useCompanyStore = defineStore('company', {
    state: () => ({
        companies: [],
        selectedCompany: localStorage.getItem('selectedCompany') ? JSON.parse(localStorage.getItem('selectedCompany')) : {},
        selectedCompanyModules: [],
    }),
    actions: {
        setSelectedCompanyById(companyId) {
            let tempCompany = this.companies.filter((company) => {
                return company.Id === parseInt(companyId);
            })[0];

            this.selectedCompany = _.omit(tempCompany, ['modules']);
            localStorage.setItem('selectedCompany', JSON.stringify(this.selectedCompany));

            this.selectedCompanyModules = tempCompany.modules;
            localStorage.setItem('selectedCompanyModules', JSON.stringify(tempCompany.modules));
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
        },

        getSelectedCompanyModuleNames() {
            return this.selectedCompanyModules.map((module) => {
                return module.Name;
            });
        }
    }
});
