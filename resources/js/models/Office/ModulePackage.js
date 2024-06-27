export default class ModulePackage {
    static getAllModulePackages() {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-packages/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModulePackages(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-packages', formData)
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
            axios.post('/api/module-package/create', formData)
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
            axios.post('/api/module-package/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(ModulePackageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-package/details', {
                ModulePackageId: ModulePackageId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ModulePackageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-package/delete', {
                ModulePackageId: ModulePackageId
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
