<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
  <div class="py-20 lg:py-32 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <!-- Hero Header -->
    <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white">
      Build modern apps with <br class="hidden sm:inline" />
      <span class="text-indigo-600 dark:text-indigo-400">Laravel & Vue 3</span>
    </h1>

    <p class="mt-6 text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
      A fast, scalable foundation for your next SaaS or Web Application powered by Pinia, Vue Router, and Tailwind CSS 4.
    </p>

    <!-- CTA Buttons -->
    <div class="mt-10 flex items-center justify-center gap-4">
      <RouterLink
        to="/register"
        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition"
      >
        Start Free Trial
      </RouterLink>
      <RouterLink
        to="/login"
        class="px-6 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition"
      >
        Live Demo
      </RouterLink>
    </div>
  </div>
</template>