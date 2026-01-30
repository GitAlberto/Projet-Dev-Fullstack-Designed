<template>
  <div class="create-review-page">
    <div class="container">
      <div class="page-header">
        <h1 class="gradient-text">✨ Créer un Avis</h1>
        <p class="page-description">Notre IA analysera votre avis automatiquement</p>
      </div>

      <div class="glass-card create-card">
        <div v-if="!submitted">
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label for="content">Votre Avis</label>
              <textarea
                id="content"
                v-model="content"
                class="form-control"
                placeholder="Partagez votre expérience..."
                rows="8"
                required
              ></textarea>
              <div class="char-count">
                {{ content.length }} caractères
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
              <span v-if="loading">
                <span class="spinner"></span>
                Analyse en cours...
              </span>
              <span v-else>🚀 Soumettre l'Avis</span>
            </button>
          </form>
        </div>

        <div v-else class="success-state">
          <div class="success-animation">
            <div class="checkmark">✓</div>
          </div>
          <h2 class="success-title">Avis créé avec succès !</h2>
          
          <div class="analysis-results">
            <div class="result-item">
              <div class="result-label">Sentiment détecté</div>
              <span :class="['badge', `badge-${getSentimentClass(result.sentiment)}`]">
                {{ getSentimentEmoji(result.sentiment) }} {{ getSentimentLabel(result.sentiment) }}
              </span>
            </div>

            <div class="result-item">
              <div class="result-label">Score d'analyse</div>
              <div class="score-display">
                <div class="score-bar">
                  <div class="score-fill" :style="{ width: result.score + '%' }"></div>
                </div>
                <span class="score-text">{{ result.score }}/100</span>
              </div>
            </div>

            <div v-if="result.topics && result.topics.length > 0" class="result-item">
              <div class="result-label">Sujets identifiés</div>
              <div class="topics-list">
                <span v-for="topic in result.topics" :key="topic" class="topic-tag">
                  #{{ topic }}
                </span>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <router-link to="/reviews" class="btn btn-primary">
              📋 Voir tous les avis
            </router-link>
            <button @click="resetForm" class="btn btn-ghost">
              ➕ Créer un autre avis
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import reviewService from '../services/reviewService'

const content = ref('')
const loading = ref(false)
const submitted = ref(false)
const result = ref(null)

const handleSubmit = async () => {
  loading.value = true

  try {
    const response = await reviewService.createReview(content.value)
    result.value = response
    submitted.value = true
  } catch (error) {
    alert('Erreur lors de la création de l\'avis')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  content.value = ''
  submitted.value = false
  result.value = null
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
</script>

<style scoped>
.create-review-page {
  padding: 40px 0;
  min-height: calc(100vh - 72px);
}

.page-header {
  text-align: center;
  margin-bottom: 40px;
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

.create-card {
  max-width: 700px;
  margin: 0 auto;
  padding: 40px;
}

.char-count {
  text-align: right;
  color: var(--text-muted);
  font-size: 12px;
  margin-top: 6px;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin-right: 8px;
}

.success-state {
  text-align: center;
}

.success-animation {
  margin-bottom: 24px;
  animation: zoomIn 0.5s ease;
}

@keyframes zoomIn {
  from {
    opacity: 0;
    transform: scale(0.5);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.checkmark {
  width: 100px;
  height: 100px;
  margin: 0 auto;
  border-radius: 50%;
  background: var(--gradient-success);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: 700;
  color: white;
  box-shadow: 0 8px 32px rgba(79, 172, 254, 0.5);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 8px 32px rgba(79, 172, 254, 0.5);
  }
  50% {
    box-shadow: 0 12px 48px rgba(79, 172, 254, 0.7);
  }
}

.success-title {
  margin-bottom: 32px;
  color: var(--text-primary);
  font-size: 24px;
  font-weight: 700;
}

.analysis-results {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--glass-border);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 32px;
  text-align: left;
}

.result-item {
  margin-bottom: 20px;
}

.result-item:last-child {
  margin-bottom: 0;
}

.result-label {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.score-display {
  display: flex;
  align-items: center;
  gap: 16px;
}

.score-bar {
  flex: 1;
  height: 8px;
  background: var(--glass-bg);
  border-radius: 4px;
  overflow: hidden;
}

.score-fill {
  height: 100%;
  background: var(--gradient-primary);
  border-radius: 4px;
  transition: width 1s ease;
  animation: fillBar 1s ease;
}

@keyframes fillBar {
  from {
    width: 0;
  }
}

.score-text {
  font-weight: 700;
  font-size: 18px;
  color: var(--text-primary);
  min-width: 60px;
}

.topics-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.topic-tag {
  background: rgba(102, 126, 234, 0.15);
  color: var(--accent-purple);
  padding: 6px 14px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid rgba(102, 126, 234, 0.3);
}

.action-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
}

@media (max-width: 768px) {
  .create-card {
    padding: 24px;
  }

  .page-header h1 {
    font-size: 32px;
  }

  .action-buttons {
    flex-direction: column;
  }
}
</style>
