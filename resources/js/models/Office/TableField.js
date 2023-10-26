export default class TableField {

    static getTableFields(TableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields', {
                TableId: TableId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getGeneralTableFields(TableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/general-table-fields', {
                TableId: TableId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCompanySpecificTableFields(TableId, CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-specific-table-fields', {
                TableId: TableId,
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

    static getCompanyAllTableFields(TableId, CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-all-table-fields', {
                TableId: TableId,
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

    static getTableFieldsOperationPreviews(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operation-sql-previews', {
                TableId: TableId,
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

    static tableFieldsOperationsSaveAndExecute(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operations-save-and-execute', {
                TableId: TableId,
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

    static tableFieldsOperationsSaveWithoutExecuting(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operations-save-without-executing', {
                TableId: TableId,
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
}
