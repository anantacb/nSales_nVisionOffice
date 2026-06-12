export default class Permission {
    static listAll() {
        return new Promise((resolve, reject) => {
            axios.post('/api/permissions/list')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getRolePermissions(RoleId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/permissions', {RoleId})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static syncRolePermissions(RoleId, PermissionIds) {
        return new Promise((resolve, reject) => {
            axios.post('/api/role/permissions/sync', {RoleId, PermissionIds})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getPermissions(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/permissions', formData ?? {})
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
            axios.post('/api/permission/create', formData)
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
            axios.post('/api/permission/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(Id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/permission/details', {Id})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static delete(Id) {
        return new Promise((resolve, reject) => {
            axios.post('/api/permission/delete', {Id})
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
