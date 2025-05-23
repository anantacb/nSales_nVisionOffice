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

    static updateItemAttributes(CompanyId, ItemId, ItemNumber, ItemAttributes = []) {
        return new Promise((resolve, reject) => {
            axios.post('/api/item-attributes/by-item/update', {
                CompanyId: CompanyId,
                ItemId: ItemId,
                ItemNumber: ItemNumber,
                ItemAttributes: ItemAttributes,
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId, ItemAttributeId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/item-attributes/delete', {
                CompanyId: CompanyId,
                ItemAttributeId: ItemAttributeId,
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
