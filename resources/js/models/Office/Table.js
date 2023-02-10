export default class Table {

    static getTables(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/tables', {...formData})
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDetails(tableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-details', {
                tableId: tableId
            })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCreatePreviewSql(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-preview-sql', {...formData})
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createTableSaveAndExecute(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-save-and-execute', {...formData})
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createTableSaveWithoutExecuting(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/create-table-save-without-executing', {
                ...formData
            })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(tableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/delete-table', {
                tableId: tableId
            })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
