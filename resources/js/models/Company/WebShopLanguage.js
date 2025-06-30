export default class WebShopLanguage {

    static fetchAll(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-languages/all', {
                CompanyId: CompanyId
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
