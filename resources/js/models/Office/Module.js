export default class Module {

    static getAllModules() {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules/all')
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
            axios.post('/api/modules/get-by-application', {
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
            axios.post('/api/modules/get-activated-and-available-modules-by-company', {
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
            axios.post('/api/modules/activate-module', {
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
            axios.post('/api/modules/deactivate-module', {
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
            axios.post('/api/modules/get-activated-modules-by-company', {
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
