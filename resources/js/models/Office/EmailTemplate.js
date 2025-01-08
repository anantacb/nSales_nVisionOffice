export default class EmailTemplate {

    static getEmailTemplates(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-template/get-email-templates', formData)
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
            axios.post('/api/email-template/create', formData)
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
            axios.post('/api/email-template/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(EmailTemplateId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-template/details', {
                EmailTemplateId: EmailTemplateId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(EmailTemplateId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-template/delete', {
                EmailTemplateId: EmailTemplateId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getEmailEvents() {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-template/get-email-events')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDataForPreview(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-template/get-data-for-preview', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

}
