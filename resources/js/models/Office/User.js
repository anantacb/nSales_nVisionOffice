export default class User {
    static login(email, password) {
        return new Promise((resolve, reject) => {
            axios.post('/api/auth/login', {
                email: email,
                password: password
            })
                .then((data) => {
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
                .then((data) => {
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
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }

    static createCompanyUser(company_id, formData) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users/company-user/create', {
                company_id, ...formData
            })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    }
}
