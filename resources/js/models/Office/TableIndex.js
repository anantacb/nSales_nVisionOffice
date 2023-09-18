export default class TableIndex {

    static getTableIndices(tableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices', {
                tableId: tableId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getTableIndicesOperationPreviews(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operation-sql-previews', {
                tableId: tableId,
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

    static tableIndicesOperationsSaveAndExecute(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operations-save-and-execute', {
                tableId: tableId,
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

    static tableIndicesOperationsSaveWithoutExecuting(tableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operations-save-without-executing', {
                tableId: tableId,
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
