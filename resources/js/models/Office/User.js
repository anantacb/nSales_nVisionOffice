export default class User {
    static login(email, password) {
        return new Promise((resolve, reject) => {
            axios.post('/api/auth/login', {
                email: email,
                password: password
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static logout() {
        return new Promise((resolve, reject) => {
            axios.post('/api/auth/logout')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getAuthUserDetails() {
        return new Promise((resolve, reject) => {
            axios.post('/api/auth/user')
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getAllCompanyUsers(CompanyId, ExcludeDevelopers = false) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/get-all-company-users', {
                CompanyId: CompanyId,
                ExcludeDevelopers: ExcludeDevelopers
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getUsers(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static details(UserId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/user/details', {
                UserId
            })
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
            axios.post('/api/user/update', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getDevelopers(formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/developers', formData)
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static getCompanyUsers(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/company-users', {
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

    static createCompanyUser(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/company-user/create', {
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

    static getCompanyUserDetails(CompanyId, UserId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/company-user/details', {
                CompanyId, UserId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static tagDeveloperToAllCompanies(UserId) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/developer/tag-developer-to-all-companies', {
                UserId
            })
                .then(({data}) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static updateCompanyUser(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/company-user/update', {
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

    static assignUserToCompany(CompanyId, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/user/assign-to-company', {
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
}
