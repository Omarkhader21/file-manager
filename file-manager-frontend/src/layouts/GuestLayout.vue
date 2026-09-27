<script setup>
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex flex-col text-slate-800 dark:text-slate-200 transition-colors duration-200">
    
    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Brand Logo -->
        <RouterLink to="/" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          My<span class="text-indigo-600 dark:text-indigo-400">App</span>
        </RouterLink>

        <!-- Dynamic Navigation Links -->
        <nav class="flex items-center gap-4">
          <template v-if="authStore.isAuthenticated">
            <RouterLink 
              to="/dashboard" 
              class="px-4 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition"
            >
              Go to Dashboard
            </RouterLink>
          </template>

          <template v-else>
            <RouterLink 
              to="/login" 
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition"
            >
              Sign In
            </RouterLink>
            <RouterLink 
              to="/register" 
              class="px-4 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-xs transition"
            >
              Get Started
            </RouterLink>
          </template>
        </nav>

      </div>
    </header>

    <!-- Dynamic Content Area -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 py-8 mt-auto">
      <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 dark:text-slate-400 space-y-2">
        <p>&copy; {{ new Date().getFullYear() }} MyCompany Inc. All rights reserved.</p>
      </div>
    </footer>

  </div>
</template>