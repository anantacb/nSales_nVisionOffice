export default class TableField {

    static getTableFields(tableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields', {
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

    static getTableFieldsOperationPreviews(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operation-sql-previews', {
                tableId: tableId,
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

    static tableFieldsOperationsSaveAndExecute(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operations-save-and-execute', {
                tableId: tableId,
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

    static tableFieldsOperationsSaveWithoutExecuting(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-fields-operations-save-without-executing', {
                tableId: tableId,
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
}
