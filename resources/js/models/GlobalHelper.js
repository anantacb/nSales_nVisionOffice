export default class TableHelper {
    static cacheClear() {
        return new Promise((resolve, reject) => {
            axios.post('/api/cache-clear')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
