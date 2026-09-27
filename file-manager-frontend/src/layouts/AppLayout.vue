<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();

// Sidebar state for mobile screens
const isMobileMenuOpen = ref(false);

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 flex">
    
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      @click="isMobileMenuOpen = false" 
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden"
    ></div>

    <!-- Sidebar Navigation -->
    <aside 
      :class="[
        'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-200 ease-in-out',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- App Brand Logo -->
      <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
        <RouterLink to="/dashboard" class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
          My<span class="text-indigo-600 dark:text-indigo-400">App</span>
        </RouterLink>
        
        <!-- Close Mobile Menu Button -->
        <button @click="isMobileMenuOpen = false" class="lg:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
          ✕
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <RouterLink 
          to="/dashboard" 
          active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <span>📊</span>
          <span>Dashboard</span>
        </RouterLink>

        <!-- Example additional link -->
        <RouterLink 
          to="/profile" 
          active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <span>👤</span>
          <span>Profile</span>
        </RouterLink>
      </nav>

      <!-- User Quick Info & Logout Footer -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="truncate">
          <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
            {{ authStore.user?.name || 'User' }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
            {{ authStore.user?.email }}
          </p>
        </div>
        <button 
          @click="handleLogout" 
          title="Logout"
          class="p-2 text-slate-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition"
        >
          🚪
        </button>
      </div>
    </aside>

    <!-- Right Side Body Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0">
      
      <!-- Top Header Navigation -->
      <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 lg:px-8 flex items-center justify-between sticky top-0 z-30">
        <!-- Mobile Sidebar Hamburger Toggle -->
        <button 
          @click="isMobileMenuOpen = !isMobileMenuOpen" 
          class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
          ☰
        </button>

        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-200 hidden sm:block">
          Overview
        </h1>

        <!-- Header Actions (e.g. Notifications / User Avatar) -->
        <div class="flex items-center gap-4">
          <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
            {{ authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
        </div>
      </header>

      <!-- Main Dynamic Content Slot -->
      <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
        <slot />
      </main>

    </div>
  </div>
</template>