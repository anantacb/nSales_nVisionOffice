export default class Onboard {
    static getCompanyOnboardStatus(CompanyId, Application) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-onboard-status', {
                CompanyId: CompanyId,
                Application
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static updateCompanyOnboardStatus(CompanyId, Application, Status) {
        return new Promise((resolve, reject) => {
            axios.post('/api/company-onboard-status/update', {
                CompanyId: CompanyId,
                Application,
                Status
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
