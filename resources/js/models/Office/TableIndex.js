export default class TableIndex {

    static getTableIndices(TableId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices', {
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

    static getTableIndicesOperationPreviews(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operation-sql-previews', {
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

    static tableIndicesOperationsSaveAndExecute(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operations-save-and-execute', {
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

    static tableIndicesOperationsSaveWithoutExecuting(TableId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-indices-operations-save-without-executing', {
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
