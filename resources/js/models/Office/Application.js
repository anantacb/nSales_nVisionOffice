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

    static getApplications(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/applications', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application/create', formData)
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
            axios.post('/api/application/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(ApplicationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application/details', {
                ApplicationId: ApplicationId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(ApplicationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/application/delete', {
                ApplicationId: ApplicationId
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
