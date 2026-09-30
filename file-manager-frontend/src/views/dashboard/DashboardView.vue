<script setup>
import { computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useFilesStore } from '@/stores/files';
import { FolderIcon } from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
const filesStore = useFilesStore();

onMounted(() => {
  filesStore.fetchFolder();
  filesStore.fetchStats();
});

const storageUsedMb = computed(() => (filesStore.stats.storageUsedBytes / 1024 / 1024).toFixed(1));
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
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ filesStore.stats.totalFiles }}</p>
      </div>
      <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">Storage Used</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ storageUsedMb }} MB</p>
      </div>
      <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">Shared Files</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ filesStore.stats.sharedFiles }}</p>
      </div>
    </div>

    <!-- Empty State / Quick Link -->
    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
      <div class="grid place-items-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 mx-auto mb-4">
        <FolderIcon class="w-6 h-6" />
      </div>
      <h3 v-if="filesStore.items.length === 0" class="font-semibold text-slate-900 dark:text-white">
        No files yet
      </h3>
      <h3 v-else class="font-semibold text-slate-900 dark:text-white">
        You have {{ filesStore.items.length }} item{{ filesStore.items.length === 1 ? '' : 's' }}
      </h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        <template v-if="filesStore.items.length === 0">
          Use "Create New" to add a folder or upload files.
        </template>
        <template v-else>
          <RouterLink to="/my-files" class="text-violet-600 dark:text-violet-400 hover:underline">
            View them in My Files
          </RouterLink>
        </template>
      </p>
    </div>
  </div>
</template>
