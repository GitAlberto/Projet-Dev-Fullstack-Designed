import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ReviewsView from '../views/ReviewsView.vue'
import CreateReviewView from '../views/CreateReviewView.vue'
import DashboardView from '../views/DashboardView.vue'

const routes = [
    {
        path: '/',
        redirect: () => {
            const authStore = useAuthStore()
            return authStore.isAdmin ? '/dashboard' : '/reviews'
        }
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: { guest: true }
    },
    {
        path: '/reviews',
        name: 'reviews',
        component: ReviewsView,
        meta: { requiresAuth: true }
    },
    {
        path: '/reviews/create',
        name: 'create-review',
        component: CreateReviewView,
        meta: { requiresAuth: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { requiresAuth: true, requiresAdmin: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/')
    } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
        next('/reviews')
    } else {
        next()
    }
})

export default router
