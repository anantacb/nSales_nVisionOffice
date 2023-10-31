export default class Table {

    static getTables(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/tables', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDetails(TableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table/details', {
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

    static getDetailsByName(TableName) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table/details-by-name', {
                TableName: TableName
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCreatePreviewSql(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-preview-sql', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createTableSaveAndExecute(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-save-and-execute', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createTableSaveWithoutExecuting(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-save-without-executing', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(TableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table/delete', {
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

    static update(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getTablesByModule(moduleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table/get-by-module', {
                moduleId: moduleId
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
