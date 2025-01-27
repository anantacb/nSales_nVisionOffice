export default class Deploy {
    static getCompanyDeploymentStatus(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-deployment-status', {
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

    static startCompanyDeployment(CompanyId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-deployment/start', {
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
