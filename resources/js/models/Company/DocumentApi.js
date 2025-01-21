export default class DocumentApi {
    static list(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-document-api', {
                CompanyId: CompanyId,
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
