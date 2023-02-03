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

    static getActivatedAndAvailableModulesByCompany(company_id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules/get-activated-and-available-modules-by-company', {
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

    static activateModule(company_id, module) {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules/activate-module', {
                company_id: company_id,
                module: module
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static deactivateModule(company_id, module) {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules/deactivate-module', {
                company_id: company_id,
                module: module
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
