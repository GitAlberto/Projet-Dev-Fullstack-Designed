import api from './api'

export default {
    async getReviews(filters = {}) {
        const params = new URLSearchParams()

        if (filters.sentiment && filters.sentiment !== 'all') {
            params.append('sentiment', filters.sentiment)
        }

        if (filters.search) {
            params.append('search', filters.search)
        }

        if (filters.page) {
            params.append('page', filters.page)
        }

        const response = await api.get(`/reviews?${params.toString()}`)
        return response.data
    },

    async getReview(id) {
        const response = await api.get(`/reviews/${id}`)
        return response.data
    },

    async createReview(content) {
        const response = await api.post('/reviews', { content })
        return response.data
    },

    async updateReview(id, content) {
        const response = await api.put(`/reviews/${id}`, { content })
        return response.data
    },

    async deleteReview(id) {
        await api.delete(`/reviews/${id}`)
    }
}
