import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const DEFAULT_TITLE = 'File Manager';

const routes = [
  // Landing Page (Uses Guest Layout, accessible by EVERYONE)
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { requiresAuth: false, title: 'Home' },
  },

  // Auth Pages (Guest Only - redirect to dashboard if already logged in)
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guestOnly: true, title: 'Login' },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { guestOnly: true, title: 'Register' },
  },

  // Protected Admin Pages
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/dashboard/DashboardView.vue'),
    meta: { requiresAuth: true, title: 'Dashboard' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' });
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }

  next();
});

router.afterEach((to) => {
  const pageTitle = to.meta.title ? `${to.meta.title} | ${DEFAULT_TITLE}` : DEFAULT_TITLE;
  document.title = pageTitle;
});

export default router;