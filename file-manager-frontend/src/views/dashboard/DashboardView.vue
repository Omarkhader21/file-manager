<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
  <div class="space-y-8">
    <!-- Welcome Banner -->
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
        Welcome back{{ authStore.user?.name ? `, ${authStore.user.name.split(' ')[0]}` : '' }}
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Here's what's happening with your files.
      </p>
    </div>

    <!-- Stat Cards -->
    <div class="grid sm:grid-cols-3 gap-4">
      <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">Total Files</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">0</p>
      </div>
      <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">Storage Used</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">0 MB</p>
      </div>
      <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">Shared Files</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">0</p>
      </div>
    </div>

    <!-- Empty State -->
    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
      <div class="grid place-items-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 mx-auto mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6">
          <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
        </svg>
      </div>
      <h3 class="font-semibold text-slate-900 dark:text-white">No files yet</h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        File uploads and management are on the way. Check back soon.
      </p>
    </div>
  </div>
</template>
