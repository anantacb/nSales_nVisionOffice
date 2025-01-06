export default class CompanyEmailTemplate {

    static getEmailTemplates(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/get-email-templates', {
                CompanyId, ...formData
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static create(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/create', {
                CompanyId, ...formData
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static update(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/update', {
                CompanyId, ...formData
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(CompanyId, EmailTemplateId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/details', {
                CompanyId: CompanyId,
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

    static delete(CompanyId, EmailTemplateId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/delete', {
                CompanyId: CompanyId,
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

    static getEmailEvents(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/get-email-events', {
                CompanyId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDataForPreview(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-template/get-data-for-preview', {
                CompanyId, ...formData
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
