export default class CompanyEmailLayout {

    static getEmailLayouts(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/get-email-layouts', {
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
            axios.post('/api/company-email-layout/create', {
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
            axios.post('/api/company-email-layout/update', {
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

    static details(CompanyId, EmailLayoutId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/details', {
                CompanyId: CompanyId,
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

    static delete(CompanyId, EmailLayoutId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/delete', {
                CompanyId: CompanyId,
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

    static getDataForPreview(CompanyId, formData) {
        console.log(CompanyId, formData);
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/get-data-for-preview', {
                CompanyId: CompanyId,
                ...formData
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getLayoutOptionsByLanguage(CompanyId, LanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/get-email-layout-options-by-language', {
                CompanyId: CompanyId,
                LanguageId: LanguageId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getPreviewTemplateObjectForLayout(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-email-layout/get-preview-template-object', {
                CompanyId: CompanyId
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
