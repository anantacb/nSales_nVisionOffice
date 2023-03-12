export default class ModuleSetting {
    static getModuleSettings(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/all-by-company', {
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

    static updateModuleSettings(CompanyId, moduleSettings) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/update-by-company', {
                CompanyId: CompanyId,
                ModuleSettings: moduleSettings
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
            axios.post('/api/module-setting/create', formData)
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
            axios.post('/api/module-setting/update', formData)
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
            axios.post('/api/module-setting/details', {
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
            axios.post('/api/module-setting/delete', {
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
