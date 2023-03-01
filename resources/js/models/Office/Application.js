export default class Application {

    static getAllApplications() {
        return new Promise((resolve, reject) => {
            axios.post('/api/applications/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
