export default class Database {

    static getAllCompanies() {
        return new Promise((resolve, reject) => {
            axios.post('/api/get-all-companies-with-db')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static copyDBtoDev(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/copy-db-to-dev', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

}
