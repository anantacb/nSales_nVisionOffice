export default class ApplicationModule {
    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application-module/create', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static update(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application-module/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ApplicationModuleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application-module/delete', {
                ApplicationModuleId: ApplicationModuleId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
