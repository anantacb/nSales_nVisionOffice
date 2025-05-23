export default class Theme {
    static themes() {
        return new Promise((resolve, reject) => {
            axios.post('/api/themes', {})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static triggerBuild(themeId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/themes/trigger-build/' + themeId, {})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
