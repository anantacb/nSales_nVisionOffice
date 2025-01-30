export default class Company {
    static getAllCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getAuthUserCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/auth-user-companies')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModuleEnabledCompanies(moduleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/by-module-enabled', {
                moduleId: moduleId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCompanies(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/companies', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getAssignableCompaniesByUser(UserId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/assignable-companies-by-user', {
                UserId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/create', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static cloneCompany(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/clone-company', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static update(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/details', {
                CompanyId: CompanyId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/delete', {
                CompanyId: CompanyId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
