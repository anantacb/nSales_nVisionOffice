export default class CustomerVisit {
    static getCustomerVisits(CompanyId, formData) {
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

    static getDistinctValue(CompanyId, columnName) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer-visits/get-distinct-value', {
                CompanyId, columnName
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
