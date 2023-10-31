export default class CustomerVisit {
    static getOrders(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer-visits', {
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

    static details(CustomerVisitId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer-visit/details', {
                CustomerVisitId: CustomerVisitId
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
