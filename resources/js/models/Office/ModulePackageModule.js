export default class ModulePackageModule {
    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-package-module/create', formData)
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
            axios.post('/api/module-package-module/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ModulePackageModuleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-package-module/delete', {
                ModulePackageModuleId: ModulePackageModuleId
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
