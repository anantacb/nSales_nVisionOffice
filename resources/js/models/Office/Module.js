export default class Module {
    static getAllModules() {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModules(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModulesByApplication(ApplicationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/get-by-application', {
                ApplicationId: ApplicationId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getActivatedAndAvailableModulesByCompany(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/get-activated-and-available-modules-by-company', {
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

    static activateModule(CompanyId, module) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/activate-module', {
                CompanyId: CompanyId, module: module
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static deactivateModule(CompanyId, module) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/deactivate-module', {
                CompanyId: CompanyId, module: module
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getActivatedModulesByCompany(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/get-activated-modules-by-company', {
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

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/create', formData)
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
            axios.post('/api/module/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(ModuleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/details', {
                ModuleId: ModuleId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ModuleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module/delete', {
                ModuleId: ModuleId
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
