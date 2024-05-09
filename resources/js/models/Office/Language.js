export default class Language {
    static getAllLanguages() {
        return new Promise((resolve, reject) => {
            axios.post('/api/languages/all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getLanguages(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/languages', formData)
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
            axios.post('/api/language/create', formData)
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
            axios.post('/api/language/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(LanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/language/details', {
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

    static delete(LanguageId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/language/delete', {
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
}
