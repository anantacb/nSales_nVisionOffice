export default class EmailConfiguration {

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-configuration/create', formData)
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
