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
            axios.post('/api/module-settings/update-by-company', {
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
}
