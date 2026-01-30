<template>
  <div class="reviews-page">
    <div class="container">
      <div class="page-header">
        <div>
          <h1 class="gradient-text">💬 Tous les Avis</h1>
          <p class="page-description">Gérez et analysez vos avis clients</p>
        </div>
        <router-link to="/reviews/create" class="btn btn-primary">
          ➕ Créer un Avis
        </router-link>
      </div>

      <!-- Filters -->
      <div class="glass-card filters">
        <div class="filter-group">
          <label>Filtrer par sentiment</label>
          <select v-model="filters.sentiment" @change="loadReviews" class="form-control">
            <option value="all">Tous les sentiments</option>
            <option value="positive">😊 Positif</option>
            <option value="neutral">😐 Neutre</option>
            <option value="negative">😞 Négatif</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Rechercher</label>
          <input
            v-model="filters.search"
            @input="handleSearch"
            type="text"
            class="form-control"
            placeholder="Rechercher dans les avis..."
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading">Chargement des avis...</div>

      <!-- Reviews Grid -->
      <div v-else-if="reviews.length > 0" class="reviews-grid">
        <div v-for="review in reviews" :key="review.id" class="glass-card review-card">
          <div class="review-header">
            <div class="review-meta">
              <div class="author-badge">
                <span class="author-icon">👤</span>
                <span class="author-name">{{ review.user?.name || 'Inconnu' }}</span>
              </div>
              <span class="review-date">{{ formatDate(review.created_at) }}</span>
            </div>
            <div class="review-badges">
              <span :class="['badge', `badge-${getSentimentClass(review.sentiment)}`]">
                {{ getSentimentEmoji(review.sentiment) }} {{ getSentimentLabel(review.sentiment) }}
              </span>
              <div class="score-badge">
                <div class="score-circle" :style="{ '--score': review.score }">
                  <span class="score-value">{{ review.score }}</span>
                </div>
              </div>
            </div>
          </div>

          <p class="review-content">{{ review.content }}</p>

          <div v-if="review.topics && review.topics.length > 0" class="review-topics">
            <span v-for="topic in review.topics" :key="topic" class="topic-tag">
              #{{ topic }}
            </span>
          </div>

          <div v-if="canEdit(review)" class="review-actions">
            <button @click="deleteReview(review.id)" class="btn btn-sm btn-danger">
              🗑️ Supprimer
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="glass-card empty-state">
        <div class="empty-icon">📭</div>
        <p class="empty-text">Aucun avis trouvé</p>
        <router-link to="/reviews/create" class="btn btn-primary">
          Créer votre premier avis
        </router-link>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-ghost"
        >
          ← Précédent
        </button>
        <span class="page-info">
          Page {{ pagination.current_page }} sur {{ pagination.last_page }}
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-ghost"
        >
          Suivant →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import reviewService from '../services/reviewService'

const authStore = useAuthStore()

const reviews = ref([])
const loading = ref(true)
const filters = ref({
  sentiment: 'all',
  search: '',
  page: 1,
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
})

let searchTimeout = null

const loadReviews = async () => {
  loading.value = true
  try {
    const response = await reviewService.getReviews(filters.value)
    reviews.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
    }
  } catch (error) {
    console.error('Échec du chargement des avis:', error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadReviews()
  }, 500)
}

const changePage = (page) => {
  filters.value.page = page
  loadReviews()
}

const getSentimentClass = (sentiment) => {
  if (sentiment === 'positive') return 'success'
  if (sentiment === 'negative') return 'danger'
  return 'warning'
}

const getSentimentLabel = (sentiment) => {
  if (sentiment === 'positive') return 'Positif'
  if (sentiment === 'negative') return 'Négatif'
  return 'Neutre'
}

const getSentimentEmoji = (sentiment) => {
  if (sentiment === 'positive') return '😊'
  if (sentiment === 'negative') return '😞'
  return '😐'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const canEdit = (review) => {
  return authStore.isAdmin || review.user_id === authStore.user?.id
}

const deleteReview = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) return

  try {
    await reviewService.deleteReview(id)
    loadReviews()
  } catch (error) {
    alert('Échec de la suppression de l\'avis')
  }
}

onMounted(() => {
  loadReviews()
})
</script>

<style scoped>
.reviews-page {
  padding: 40px 0;
  min-height: calc(100vh - 72px);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  gap: 20px;
}

.page-header h1 {
  margin: 0 0 8px 0;
  font-size: 42px;
  font-weight: 800;
  letter-spacing: -1px;
}

.page-description {
  color: var(--text-secondary);
  font-size: 16px;
}

.filters {
  display: flex;
  gap: 20px;
  margin-bottom: 32px;
}

.filter-group {
  flex: 1;
}

.filter-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--text-secondary);
  font-size: 14px;
}

.reviews-grid {
  display: grid;
  gap: 24px;
  margin-bottom: 32px;
}

.review-card {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.review-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--gradient-primary);
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.review-card:hover::before {
  transform: scaleY(1);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--glass-border);
}

.review-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.author-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.author-icon {
  font-size: 16px;
}

.author-name {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 15px;
}

.review-date {
  font-size: 13px;
  color: var(--text-muted);
}

.review-badges {
  display: flex;
  gap: 12px;
  align-items: center;
}

.score-badge {
  position: relative;
}

.score-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: conic-gradient(
    var(--accent-purple) 0%,
    var(--accent-purple) calc(var(--score) * 1%),
    var(--glass-bg) calc(var(--score) * 1%),
    var(--glass-bg) 100%
  );
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.score-circle::before {
  content: '';
  position: absolute;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--bg-secondary);
}

.score-value {
  position: relative;
  z-index: 1;
  font-weight: 700;
  font-size: 14px;
  color: var(--text-primary);
}

.review-content {
  margin: 16px 0;
  color: var(--text-secondary);
  line-height: 1.7;
  font-size: 15px;
}

.review-topics {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 16px 0;
}

.topic-tag {
  background: rgba(102, 126, 234, 0.15);
  color: var(--accent-purple);
  padding: 6px 14px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
}

.topic-tag:hover {
  background: rgba(102, 126, 234, 0.25);
  transform: translateY(-2px);
}

.review-actions {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--glass-border);
}

.empty-state {
  text-align: center;
  padding: 80px 40px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.empty-text {
  color: var(--text-secondary);
  margin-bottom: 24px;
  font-size: 18px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
}

.page-info {
  color: var(--text-secondary);
  font-weight: 500;
  font-size: 14px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .page-header h1 {
    font-size: 32px;
  }

  .filters {
    flex-direction: column;
  }

  .review-header {
    flex-direction: column;
    gap: 12px;
  }

  .review-badges {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
