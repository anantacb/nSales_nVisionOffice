export default class EmailLayout {

    static getEmailLayouts(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/get-email-layouts', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getEmailLayoutsForCompany(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/get-email-layouts-for-company', {
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

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/create', formData)
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
            axios.post('/api/email-layout/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(EmailLayoutId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/details', {
                EmailLayoutId: EmailLayoutId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(EmailLayoutId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/delete', {
                EmailLayoutId: EmailLayoutId
            })
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
            axios.post('/api/email-layout/get-data-for-preview', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getLayoutOptionsByLanguage(LanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/get-email-layout-options-by-language', {
                LanguageId:LanguageId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getPreviewTemplateObjectForLayout() {
        return new Promise((resolve, reject) => {
            axios.post('/api/email-layout/get-preview-template-object')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

}
