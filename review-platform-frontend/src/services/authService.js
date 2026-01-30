import api from './api'

export default {
    async login(email, password) {
        const response = await api.post('/login', { email, password })
        return response.data
    },

    async register(name, email, password, password_confirmation) {
        const response = await api.post('/register', {
            name,
            email,
            password,
            password_confirmation
        })
        return response.data
    },

    async logout() {
        await api.post('/logout')
    },

    async me() {
        const response = await api.get('/me')
        return response.data
    }
}
