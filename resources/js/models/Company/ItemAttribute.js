export default class ItemAttribute {

    static fetchByItem(CompanyId, ItemId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/item-attributes/by-item/get', {
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


}
