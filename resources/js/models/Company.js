export default class Company {

    static getAllCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/companies/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getModuleEnabledCompanies(moduleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/companies/by-module-enabled', {
                moduleId: moduleId
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
