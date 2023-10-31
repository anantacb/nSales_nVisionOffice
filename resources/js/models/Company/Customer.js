export default class Customer {
    static getCustomers(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customers', {
                CompanyId, ...formData
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
            axios.post('/api/customer/create', formData)
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
            axios.post('/api/customer/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(CompanyId, CustomerId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer/details', {
                CompanyId: CompanyId,
                CustomerId: CustomerId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId, CustomerId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer/delete', {
                CompanyId: CompanyId,
                CustomerId: CustomerId
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
