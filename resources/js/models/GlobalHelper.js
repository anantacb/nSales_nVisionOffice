export default class GlobalHelper {
    static cacheClear(fromData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/cache-clear', fromData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
