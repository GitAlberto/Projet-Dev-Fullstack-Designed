import { defineStore } from 'pinia'
import authService from '../services/authService'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin'
    },

    actions: {
        async login(email, password) {
            const data = await authService.login(email, password)
            this.token = data.token
            this.user = data.user
            localStorage.setItem('token', data.token)
        },

        async register(name, email, password, password_confirmation) {
            const data = await authService.register(name, email, password, password_confirmation)
            this.token = data.token
            this.user = data.user
            localStorage.setItem('token', data.token)
        },

        async logout() {
            try {
                await authService.logout()
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                this.user = null
                this.token = null
                localStorage.removeItem('token')
            }
        },

        async loadUser() {
            if (!this.token) return

            try {
                this.user = await authService.me()
            } catch (error) {
                console.error('Load user error:', error)
                this.logout()
            }
        }
    }
})
