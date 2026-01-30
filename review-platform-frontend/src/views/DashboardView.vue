<template>
  <div class="dashboard-page">
    <div class="container">
      <div class="page-header">
        <div>
          <h1 class="gradient-text">📊 Tableau de bord</h1>
          <p class="page-description">Vue d'ensemble des statistiques</p>
        </div>
      </div>

      <div v-if="loading" class="loading">Chargement des statistiques...</div>

      <div v-else-if="stats" class="dashboard-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
          <div class="glass-card stat-card">
            <div class="stat-icon" style="background: var(--gradient-primary)">📊</div>
            <div class="stat-info">
              <h3>Total des Avis</h3>
              <p class="stat-value">{{ stats.total_reviews }}</p>
            </div>
          </div>

          <div class="glass-card stat-card">
            <div class="stat-icon" style="background: var(--gradient-warning)">⭐</div>
            <div class="stat-info">
              <h3>Score Moyen</h3>
              <p class="stat-value">{{ stats.average_score }}</p>
            </div>
          </div>

          <div class="glass-card stat-card positive">
            <div class="stat-icon" style="background: var(--gradient-success)">😊</div>
            <div class="stat-info">
              <h3>Positifs</h3>
              <p class="stat-value">{{ stats.positive_percentage }}%</p>
            </div>
          </div>

          <div class="glass-card stat-card negative">
            <div class="stat-icon" style="background: var(--gradient-danger)">😞</div>
            <div class="stat-info">
              <h3>Négatifs</h3>
              <p class="stat-value">{{ stats.negative_percentage }}%</p>
            </div>
          </div>
        </div>

        <!-- Top Topics -->
        <div class="glass-card top-topics">
          <h2>🏷️ Top Sujets</h2>
          <div v-if="stats.top_topics.length > 0" class="topics-chart">
            <div
              v-for="(item, index) in stats.top_topics"
              :key="item.topic"
              class="topic-bar"
              :style="{ animationDelay: index * 0.1 + 's' }"
            >
              <div class="topic-info">
                <span class="topic-name">{{ item.topic }}</span>
                <span class="topic-count">{{ item.count }} mentions</span>
              </div>
              <div class="bar-container">
                <div 
                  class="bar-fill" 
                  :style="{ 
                    width: (item.count / Math.max(...stats.top_topics.map(t => t.count)) * 100) + '%',
                    animationDelay: index * 0.1 + 's'
                  }"
                ></div>
              </div>
            </div>
          </div>
          <p v-else class="no-data">Aucun sujet détecté pour le moment</p>
        </div>

        <!-- Recent Reviews -->
        <div class="glass-card recent-reviews">
          <h2>📝 Avis Récents</h2>
          <div v-if="stats.recent_reviews.length > 0" class="reviews-timeline">
            <div
              v-for="(review, index) in stats.recent_reviews"
              :key="review.id"
              class="timeline-item"
              :style="{ animationDelay: index * 0.1 + 's' }"
            >
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <div class="review-header">
                  <span class="review-user">👤 {{ review.user_name }}</span>
                  <span :class="['badge', `badge-${getSentimentClass(review.sentiment)}`]">
                    {{ getSentimentLabel(review.sentiment) }}
                  </span>
                </div>
                <p class="review-text">{{ review.content }}</p>
                <div class="review-footer">
                  <span class="review-score">Score : {{ review.score }}/100</span>
                  <span class="review-date">{{ review.created_at }}</span>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="no-data">Aucun avis pour le moment</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import dashboardService from '../services/dashboardService'

const stats = ref(null)
const loading = ref(true)

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

const loadStats = async () => {
  try {
    stats.value = await dashboardService.getStatistics()
  } catch (error) {
    console.error('Échec du chargement des statistiques:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStats()
})
</script>

<style scoped>
.dashboard-page {
  padding: 40px 0;
  min-height: calc(100vh - 72px);
}

.page-header {
  margin-bottom: 32px;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 28px;
  position: relative;
  overflow: hidden;
  animation: slideUp 0.5s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: var(--gradient-primary);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.stat-card:hover::before {
  transform: scaleX(1);
}

.stat-card.positive::before {
  background: var(--gradient-success);
}

.stat-card.negative::before {
  background: var(--gradient-danger);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.stat-info h3 {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
  line-height: 1;
}

.top-topics,
.recent-reviews {
  margin-bottom: 32px;
  padding: 32px;
}

h2 {
  margin-bottom: 24px;
  color: var(--text-primary);
  font-size: 22px;
  font-weight: 700;
}

.topics-chart {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.topic-bar {
  animation: slideUp 0.5s ease backwards;
}

.topic-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.topic-name {
  font-weight: 600;
  color: var(--text-primary);
  text-transform: capitalize;
  font-size: 15px;
}

.topic-count {
  font-size: 13px;
  color: var(--text-muted);
}

.bar-container {
  height: 10px;
  background: var(--glass-bg);
  border-radius: 5px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: var(--gradient-primary);
  border-radius: 5px;
  animation: fillBar 1s ease backwards;
}

@keyframes fillBar {
  from {
    width: 0 !important;
  }
}

.reviews-timeline {
  position: relative;
  padding-left: 30px;
}

.reviews-timeline::before {
  content: '';
  position: absolute;
  left: 9px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--glass-border);
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
  animation: slideUp 0.5s ease backwards;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -26px;
  top: 8px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--gradient-accent);
  border: 3px solid var(--bg-secondary);
  box-shadow: 0 0 0 3px var(--glass-border);
}

.timeline-content {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--glass-border);
  border-radius: 12px;
  padding: 16px;
  transition: all 0.3s ease;
}

.timeline-content:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.15);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.review-user {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.review-text {
  margin: 12px 0;
  color: var(--text-secondary);
  line-height: 1.6;
  font-size: 14px;
}

.review-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: var(--text-muted);
}

.review-score {
  font-weight: 600;
}

.no-data {
  text-align: center;
  color: var(--text-muted);
  padding: 40px 20px;
  font-size: 15px;
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 32px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .top-topics,
  .recent-reviews {
    padding: 24px;
  }
}
</style>
