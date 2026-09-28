<script setup>
import { RouterLink } from 'vue-router';

const features = [
  {
    title: 'Organize everything',
    text: 'Folders, tags, and instant search keep every file exactly where you expect it.',
    icon: 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z',
  },
  {
    title: 'Share with control',
    text: 'Send a link, set an expiry, and know exactly who has access at all times.',
    icon: 'M8.68 13.34a3 3 0 1 0 0-2.68m0 2.68 6.64 3.32m-6.64-6 6.64-3.32M18 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm0 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM8 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z',
  },
  {
    title: 'Built to scale',
    text: 'A Laravel + Vue foundation that stays fast whether you have ten files or ten million.',
    icon: 'M13 10V3L4 14h7v7l9-11h-7Z',
  },
];
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="py-20 lg:py-32 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-violet-100 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300 mb-6">
        Now in early access
      </span>

      <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white">
        All your files, <br class="hidden sm:inline" />
        <span class="text-violet-600 dark:text-violet-400">one calm place</span>
      </h1>

      <p class="mt-6 text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
        Upload, organize, and share your files from a fast, clutter-free workspace built for individuals and teams.
      </p>

      <div class="mt-10 flex items-center justify-center gap-4">
        <RouterLink
          to="/register"
          class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-xl shadow-lg shadow-violet-600/20 transition"
        >
          Get Started Free
        </RouterLink>
        <RouterLink
          to="/login"
          class="px-6 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition"
        >
          Sign In
        </RouterLink>
      </div>
    </section>

    <!-- Features -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
      <div class="grid sm:grid-cols-3 gap-6">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm"
        >
          <div class="grid place-items-center w-10 h-10 rounded-lg bg-violet-600 text-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
            </svg>
          </div>
          <h3 class="font-semibold text-slate-900 dark:text-white mb-1.5">{{ feature.title }}</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">{{ feature.text }}</p>
        </div>
      </div>
    </section>
  </div>
</template>
