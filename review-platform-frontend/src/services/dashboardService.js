import api from './api'

export default {
    async getStatistics() {
        const response = await api.get('/dashboard/stats')
        return response.data
    }
}
