export default class Company {
    static getAllCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/all')
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
            axios.post('/api/company/by-module-enabled', {
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

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company/create', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
