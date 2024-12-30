export default class ModuleSetting {
    static getModuleSettingsByCompany(CompanyId) {
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

    static getModuleSettingsByName(CompanyId, Settings) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/by-name', {
                CompanyId: CompanyId,
                Settings
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCodeModuleSettingsByName(Module, SettingKeys) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/core-settings-by-name', {
                Module: Module,
                SettingKeys: SettingKeys,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModuleSettings(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-settings', formData)
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

    static details(ModuleSettingId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/details', {
                ModuleSettingId: ModuleSettingId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ModuleSettingId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/delete', {
                ModuleSettingId: ModuleSettingId
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
