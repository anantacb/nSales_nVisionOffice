export default class Role {
    static getRolesByCompany(company_id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/roles/by-company', {
                company_id: company_id
            })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
