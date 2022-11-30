export default class Company {

    static getAllCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/companies/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
