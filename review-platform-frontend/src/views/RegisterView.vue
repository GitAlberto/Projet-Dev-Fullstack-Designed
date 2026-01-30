<template>
  <div class="register-page">
    <div class="register-container">
      <div class="glass-card register-card">
        <h1 class="gradient-text">Créer un compte</h1>
        <p class="subtitle">Rejoignez-nous dès maintenant</p>

        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <form @submit.prevent="handleRegister">
          <div class="form-group">
            <label for="name">Nom complet</label>
            <input
              id="name"
              v-model="name"
              type="text"
              class="form-control"
              placeholder="Entrez votre nom"
              required
            />
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="form-control"
              placeholder="Entrez votre email"
              required
            />
          </div>

          <div class="form-group">
            <label for="password">Mot de passe</label>
            <input
              id="password"
              v-model="password"
              type="password"
              class="form-control"
              placeholder="Entrez un mot de passe"
              required
            />
          </div>

          <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              type="password"
              class="form-control"
              placeholder="Confirmez votre mot de passe"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
            <span v-if="loading">
              <span class="spinner"></span>
              Inscription...
            </span>
            <span v-else>S'inscrire</span>
          </button>
        </form>

        <p class="login-link">
          Déjà un compte ? <router-link to="/login" class="gradient-text-accent">Connectez-vous ici</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
  error.value = ''

  if (password.value !== passwordConfirmation.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }

  loading.value = true

  try {
    await authStore.register(
      name.value,
      email.value,
      password.value,
      passwordConfirmation.value
    )
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de l\'inscription'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: 
    radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
    radial-gradient(circle at 80% 50%, rgba(245, 87, 108, 0.15) 0%, transparent 50%);
}

.register-container {
  width: 100%;
  max-width: 450px;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.register-card {
  padding: 40px;
}

h1 {
  margin-bottom: 8px;
  text-align: center;
  font-size: 36px;
  font-weight: 800;
  letter-spacing: -1px;
}

.subtitle {
  text-align: center;
  color: var(--text-secondary);
  margin-bottom: 30px;
  font-size: 15px;
}

.login-link {
  text-align: center;
  margin-top: 24px;
  color: var(--text-secondary);
  font-size: 14px;
}

.login-link a {
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.login-link a:hover {
  opacity: 0.8;
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

@media (max-width: 480px) {
  .register-card {
    padding: 30px 24px;
  }

  h1 {
    font-size: 28px;
  }
}
</style>
