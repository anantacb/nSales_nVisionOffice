export default class Table {

    static getTables(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/tables', {...formData})
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
