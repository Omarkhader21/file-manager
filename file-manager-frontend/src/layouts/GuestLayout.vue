<script setup>
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { FolderIcon } from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 relative overflow-hidden">

    <!-- Decorative background glow -->
    <div class="pointer-events-none absolute inset-x-0 -top-40 -z-10 flex justify-center blur-3xl">
      <div class="aspect-1155/678 w-[72rem] bg-linear-to-tr from-violet-400 to-sky-300 opacity-20 dark:opacity-10 [clip-path:polygon(74%_44%,100%_61%,97%_26%,85%_0%,80%_9%,72%_53%,60%_29%,32%_31%,0%_54%,15%_100%,25%_61%,44%_35%)]"></div>
    </div>

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200/80 dark:border-slate-800 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <!-- Brand Logo -->
        <RouterLink to="/" class="flex items-center gap-2 font-bold tracking-tight text-slate-900 dark:text-white">
          <span class="grid place-items-center w-8 h-8 rounded-lg bg-violet-600 text-white">
            <FolderIcon class="w-4.5 h-4.5" />
          </span>
          <span class="text-lg">File<span class="text-violet-600 dark:text-violet-400">Manager</span></span>
        </RouterLink>

        <!-- Dynamic Navigation Links -->
        <nav class="flex items-center gap-2 sm:gap-3">
          <ThemeToggle />

          <template v-if="authStore.isAuthenticated">
            <RouterLink
              to="/dashboard"
              class="px-4 py-2 text-sm font-medium bg-violet-600 hover:bg-violet-700 text-white rounded-lg shadow-sm shadow-violet-600/20 transition"
            >
              Go to Dashboard
            </RouterLink>
          </template>

          <template v-else>
            <RouterLink
              to="/login"
              class="px-3.5 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 transition"
            >
              Sign In
            </RouterLink>
            <RouterLink
              to="/register"
              class="px-4 py-2 text-sm font-medium bg-violet-600 hover:bg-violet-700 text-white rounded-lg shadow-sm shadow-violet-600/20 transition"
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
      <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 dark:text-slate-400">
        <p>&copy; {{ new Date().getFullYear() }} FileManager. All rights reserved.</p>
      </div>
    </footer>

  </div>
</template>
