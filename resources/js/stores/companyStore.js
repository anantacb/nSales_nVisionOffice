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
            let tempCompany = this.companies.find((company) => {
                return company.Id === parseInt(companyId);
            });

            // The requested Id may not exist in the current user's company list
            // (revoked access, stale/shared-browser localStorage, or no match at all).
            // Fall back to the first company this user actually has access to instead
            // of leaving selectedCompany undefined, which would throw below.
            if (!tempCompany) {
                tempCompany = this.companies[0];
            }

            this.selectedCompany = tempCompany ?? {};

            if (!this.selectedCompany.Id) {
                // The user has no companies at all - nothing to select or persist.
                localStorage.removeItem('selectedCompany');
                const authStore = useAuthStore();
                authStore.setRoles([]);
                authStore.setPermissions([]);
                this.selectedCompanyModules = [];
                return;
            }

            localStorage.setItem('selectedCompany', JSON.stringify(this.selectedCompany));

            const authStore = useAuthStore();
            authStore.setRoles(this.selectedCompany.roles);
            authStore.setPermissions(this.selectedCompany.permissions ?? []);

            let {data} = await Module.getActivatedModulesByCompany(this.selectedCompany.Id);
            this.selectedCompanyModules = data;
            localStorage.setItem('selectedCompanyModules', JSON.stringify(data));
        },

        async fill() {
            //let {data} = await Company.getAllCompanies();
            let {data} = await Company.getAuthUserCompanies();
            this.companies = data;
            const persistedSelectedCompany = localStorage.getItem('selectedCompany');
            if (persistedSelectedCompany) {
                await this.setSelectedCompanyById(JSON.parse(persistedSelectedCompany).Id);
            } else {
                await this.setSelectedCompanyById(this.companies[0]?.Id);
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
