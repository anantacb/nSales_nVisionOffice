export default class B2bGqlApi {
    static getItemGroupsAndItem(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/b2b-gql-api/get-itemgroups-item', {
                CompanyId: CompanyId
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
