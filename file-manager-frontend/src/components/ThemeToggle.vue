<script setup>
import { useThemeStore } from '@/stores/theme';
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline';

const themeStore = useThemeStore();
</script>

<template>
  <button
    @click="themeStore.toggle()"
    :title="themeStore.theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
    class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition"
  >
    <MoonIcon v-if="themeStore.theme === 'dark'" class="w-5 h-5" />
    <SunIcon v-else class="w-5 h-5" />
  </button>
</template>
