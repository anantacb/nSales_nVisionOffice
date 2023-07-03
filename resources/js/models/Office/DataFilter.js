export default class DataFilter {

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filter/create', formData)
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
            axios.post('/api/data-filter/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDataFilters(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filters', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getFilterResult(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filters/get-filter-result', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCompanyDataFilters(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filters/company-data-filters', {
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

    static delete(DataFilterId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filter/delete', {
                DataFilterId: DataFilterId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(DataFilterId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/data-filter/details', {
                DataFilterId: DataFilterId
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
