export default class Git {
    static getCompanyBranches(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-git-branches', {
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

    static createCompanyBranches(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-git-branches/create', {
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
