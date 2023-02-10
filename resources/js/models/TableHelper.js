export default class TableHelper {
    static getEnumValues(database_type, table, column, domain = '') {
        return new Promise((resolve, reject) => {
            axios.post('/api/table-helper/get-enum-values', {
                database_type: database_type,
                table: table,
                column: column,
                domain: domain
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
