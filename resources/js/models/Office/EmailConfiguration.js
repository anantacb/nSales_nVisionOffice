export default class EmailConfiguration {

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-configuration/create', formData)
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
            axios.post('/api/email-configuration/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getEmailConfigurations(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-configurations', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(EmailConfigurationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-configuration/delete', {
                EmailConfigurationId: EmailConfigurationId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(EmailConfigurationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-configuration/details', {
                EmailConfigurationId: EmailConfigurationId
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
