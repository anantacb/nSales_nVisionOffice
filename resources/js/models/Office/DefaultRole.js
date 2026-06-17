export default class DefaultRole {
    static getDefaultRoles(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/default-roles/list', formData ?? {})
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
            axios.post('/api/default-role/create', formData)
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
            axios.post('/api/default-role/update', formData)
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
            axios.post('/api/default-role/delete', {RoleId})
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
            axios.post('/api/default-role/details', {RoleId})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static syncPermissionsToCompanyRoles(Id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/default-role/permissions/sync', {Id})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static syncAllPermissionsToCompanyRoles() {
        return new Promise((resolve, reject) => {
            axios.post('/api/default-roles/permissions/sync-all')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
