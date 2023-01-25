export default class Module {

    static getAllModules() {
        return new Promise((resolve, reject) => {
            axios.post('/api/modules/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
