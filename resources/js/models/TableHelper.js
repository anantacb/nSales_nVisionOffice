export default class TableHelper {
    static getEnumValues(DatabaseType, TableName, ColumnName, CompanyId = null) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-helper/get-enum-values', {
                DatabaseType: DatabaseType,
                TableName: TableName,
                ColumnName: ColumnName,
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

    static getColumnDistinctValues(DatabaseType, TableName, ColumnName, CompanyId = null) {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-helper/get-column-distinct-values', {
                DatabaseType: DatabaseType,
                TableName: TableName,
                ColumnName: ColumnName,
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
