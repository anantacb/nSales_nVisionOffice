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
}
