export default class WebShopPage {
    static list(CompanyId, Platform) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-page/list', {
                CompanyId,
                Platform
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createPagesC(CompanyId, pages) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-page/create-pages', {
                CompanyId,
                pages
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createPagesContentForMissingLanguages(CompanyId, pages) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-page/create-pages-content-for-missing-languages', {
                CompanyId,
                pages
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
