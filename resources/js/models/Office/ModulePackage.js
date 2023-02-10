export default class ModulePackage {
    static getAllModulePackages() {
        return new Promise((resolve, reject) => {
            axios.post('/api/module-package/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
