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

  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/profile/UserProfileView.vue'),
    meta: { requiresAuth: true, title: 'Profile' },
  },
  {
    path: '/my-files/:id?',
    name: 'my-files',
    component: () => import('@/views/files/MyFilesView.vue'),
    meta: { requiresAuth: true, title: 'My Files' },
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' };
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return { name: 'dashboard' };
  }
});

router.afterEach((to) => {
  const pageTitle = to.meta.title ? `${to.meta.title} | ${DEFAULT_TITLE}` : DEFAULT_TITLE;
  document.title = pageTitle;
});

export default router;