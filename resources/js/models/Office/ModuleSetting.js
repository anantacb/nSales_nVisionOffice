export default class ModuleSetting {
    static getModuleSettings(company_id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-setting/all-by-company', {
                company_id: company_id
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static updateModuleSettings(company_id, moduleSettings) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-settings/update-by-company', {
                company_id: company_id,
                module_settings: moduleSettings
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
