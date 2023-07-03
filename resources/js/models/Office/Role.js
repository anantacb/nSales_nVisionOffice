export default class Role {
    static getRolesByCompany(CompanyId, WithDeveloper = false) {
        return new Promise((resolve, reject) => {
            axios.post('/api/roles/by-company', {
                CompanyId: CompanyId,
                WithDeveloper: WithDeveloper
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
