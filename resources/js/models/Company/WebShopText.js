export default class WebShopText {

    static fetchByItem(CompanyId, ItemId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-text/get-web-shop-texts-by-item', {
                CompanyId: CompanyId,
                ItemId: ItemId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static updateByItem(formData) {

        return new Promise((resolve, reject) => {
            axios.post('/api/web-shop-text/update-by-item', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

}
