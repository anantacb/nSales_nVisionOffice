export default class CompanyTranslation {

    static getCompanyTranslations(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-translations', {
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
            axios.post('/api/company-translation/create', {
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
            axios.post('/api/company-translation/update', {
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

    static details(CompanyId, CompanyTranslationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-translation/details', {
                CompanyId: CompanyId,
                CompanyTranslationId: CompanyTranslationId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId, CompanyTranslationId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-translation/delete', {
                CompanyId: CompanyId,
                CompanyTranslationId: CompanyTranslationId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static syncCompanyTranslations(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-translations/sync', {
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
