export default class WebShopUser {
    static details(CompanyId, Key, Value) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-user/details', {
                CompanyId: CompanyId,
                Key,
                Value
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createTestUser(CompanyId, request) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-user/create-test-user', {
                CompanyId: CompanyId,
                ...request
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
