export default class CompanyLanguage {

    static getAllCompanyLanguages(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-languages/all', {
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

    static getCompanyLanguages(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-languages', {
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

    static addCompanyLanguage(CompanyId, LanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-language/add-company-language', {
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

    static setAsDefaultLanguage(CompanyId, CompanyLanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-language/set-as-default-language', {
                CompanyId: CompanyId,
                CompanyLanguageId: CompanyLanguageId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(CompanyId, CompanyLanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-language/delete', {
                CompanyId: CompanyId,
                CompanyLanguageId: CompanyLanguageId
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
