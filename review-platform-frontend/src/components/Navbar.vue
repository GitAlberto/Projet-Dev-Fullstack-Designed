<template>
  <nav class="navbar">
    <div class="container navbar-content">
      <div class="navbar-brand">
        <h2 class="gradient-text">✨ Analyse d'Avis</h2>
      </div>
      
      <div class="navbar-links">
        <router-link v-if="authStore.isAdmin" to="/dashboard" class="nav-link">
          <span class="nav-icon">📊</span>
          Tableau de bord
        </router-link>
        <router-link to="/reviews" class="nav-link">
          <span class="nav-icon">💬</span>
          Avis
        </router-link>
        <router-link to="/reviews/create" class="nav-link">
          <span class="nav-icon">➕</span>
          Nouvel Avis
        </router-link>
      </div>
      
      <div class="navbar-user">
        <div class="user-avatar">{{ getUserInitials() }}</div>
        <span class="user-name">{{ authStore.user?.name }}</span>
        <span v-if="authStore.isAdmin" class="badge badge-warning">Admin</span>
        <button @click="handleLogout" class="btn btn-sm btn-ghost">
          Déconnexion
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const getUserInitials = () => {
  const name = authStore.user?.name || ''
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--glass-border);
  padding: 16px 0;
  margin-bottom: 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.navbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.navbar-brand h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.navbar-links {
  display: flex;
  gap: 8px;
  flex: 1;
  margin-left: 40px;
}

.nav-link {
  color: var(--text-secondary);
  text-decoration: none;
  font-weight: 500;
  padding: 10px 16px;
  border-radius: 10px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
}

.nav-icon {
  font-size: 16px;
}

.nav-link::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2px;
  background: var(--gradient-primary);
  transform: translateX(-50%);
  transition: width 0.3s ease;
}

.nav-link:hover {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.05);
}

.nav-link:hover::before {
  width: 80%;
}

.nav-link.router-link-active {
  color: var(--text-primary);
  background: var(--gradient-primary);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.nav-link.router-link-active::before {
  display: none;
}

.navbar-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--gradient-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: white;
  box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
}

.user-name {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

@media (max-width: 768px) {
  .navbar-content {
    flex-wrap: wrap;
  }

  .navbar-links {
    margin-left: 0;
    order: 3;
    width: 100%;
    flex-wrap: wrap;
  }

  .user-name {
    display: none;
  }
}
</style>
