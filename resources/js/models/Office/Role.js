export default class Role {
    static getRolesByCompany(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/roles/by-company', {
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
