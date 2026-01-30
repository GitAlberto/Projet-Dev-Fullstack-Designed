<template>
  <div class="login-page">
    <div class="login-container">
      <div class="glass-card login-card">
        <h1 class="gradient-text">Bienvenue</h1>
        <p class="subtitle">Connectez-vous à votre compte</p>

        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <form @submit.prevent="handleLogin">
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
              placeholder="Entrez votre mot de passe"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
            <span v-if="loading">
              <span class="spinner"></span>
              Connexion...
            </span>
            <span v-else>Se connecter</span>
          </button>
        </form>

        <p class="register-link">
          Pas de compte ? <router-link to="/register" class="gradient-text-accent">Inscrivez-vous ici</router-link>
        </p>

        <div class="demo-credentials glass-card">
          <p class="demo-title"><strong>🔑 Comptes de démonstration</strong></p>
          <p>👨‍💼 Admin : <code>admin@example.com</code> / <code>password</code></p>
          <p>👤 Utilisateur : <code>user@example.com</code> / <code>password</code></p>
        </div>
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

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    await authStore.login(email.value, password.value)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Email ou mot de passe invalide'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: 
    radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
    radial-gradient(circle at 80% 50%, rgba(245, 87, 108, 0.15) 0%, transparent 50%);
}

.login-container {
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

.login-card {
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

.register-link {
  text-align: center;
  margin-top: 24px;
  color: var(--text-secondary);
  font-size: 14px;
}

.register-link a {
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.register-link a:hover {
  opacity: 0.8;
}

.demo-credentials {
  margin-top: 30px;
  padding: 20px;
  font-size: 13px;
  text-align: left;
  background: rgba(255, 255, 255, 0.02);
}

.demo-title {
  margin-bottom: 12px;
  color: var(--text-primary);
  text-align: center;
}

.demo-credentials p {
  margin: 8px 0;
  color: var(--text-secondary);
}

.demo-credentials code {
  background: rgba(102, 126, 234, 0.2);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 12px;
  color: var(--accent-purple);
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
  .login-card {
    padding: 30px 24px;
  }

  h1 {
    font-size: 28px;
  }
}
</style>
