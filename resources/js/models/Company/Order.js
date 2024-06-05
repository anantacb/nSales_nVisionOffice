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
                CompanyId: CompanyId,
                UUID: OrderUUID,
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
                CompanyId: CompanyId,
                UUID: OrderUUID,
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

    // Get latest Orders by Customer
    static getLatestOrdersByCustomer(CompanyId, CustomerId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/customer/latest/orders', {
                CompanyId: CompanyId,
                CustomerId: CustomerId,
                //initials: initials
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static reExportOrder(CompanyId, OrderUUID) {
        return new Promise((resolve, reject) => {
            axios.post('/api/order/re-export', {
                CompanyId: CompanyId,
                UUID: OrderUUID,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }


    static fetchSalesYearlyByItem(CompanyId, ItemId, Initials = '') {
        return new Promise((resolve, reject) => {
            axios.post('/api/item/total-sales-yearly', {
                CompanyId: CompanyId,
                ItemId: ItemId,
                Initials: Initials
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static fetchSalesMonthlyByItem(CompanyId, ItemId, Initials = '') {
        return new Promise((resolve, reject) => {
            axios.post('/api/item/total-sales-monthly', {
                CompanyId: CompanyId,
                ItemId: ItemId,
                Initials: Initials
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static fetchOrdersYearlyByItem(CompanyId, ItemId, Initials = '') {
        return new Promise((resolve, reject) => {
            axios.post('/api/item/quantity-orders-yearly', {
                CompanyId: CompanyId,
                ItemId: ItemId,
                Initials: Initials
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static fetchOrdersMonthlyByItem(CompanyId, ItemId, Initials = '') {
        return new Promise((resolve, reject) => {
            axios.post('/api/item/quantity-orders-monthly', {
                CompanyId: CompanyId,
                ItemId: ItemId,
                Initials: Initials
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
