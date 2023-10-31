export default class Role {
    static getRolesByCompany(CompanyId, WithDeveloper = false) {
        return new Promise((resolve, reject) => {
            axios.post('/api/roles/by-company', {
                CompanyId: CompanyId,
                WithDeveloper: WithDeveloper
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static create(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/create', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static update(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCompanyRoles(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/roles/company-roles', {
                CompanyId, ...formData
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(RoleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/delete', {
                RoleId: RoleId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(RoleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/details', {
                RoleId: RoleId
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
