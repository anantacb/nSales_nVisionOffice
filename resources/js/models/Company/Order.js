export default class Order {
    static getOrders(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/orders', {
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

    static getOpenOrders(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/open-orders', {
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

    static getFailedOrders(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/failed-orders', {
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

    static details(CompanyId, OrderUUID) {
        return new Promise((resolve, reject) => {
            axios.post('/api/order/details', {
                UUID: OrderUUID,
                CompanyId: CompanyId,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId, OrderUUID) {
        return new Promise((resolve, reject) => {
            axios.post('/api/order/delete', {
                UUID: OrderUUID,
                CompanyId: CompanyId,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getOrderOrigins(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/order/origins-get', {
                CompanyId: CompanyId,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });

    }
}
