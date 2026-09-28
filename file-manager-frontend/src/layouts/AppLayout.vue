<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import ThemeToggle from '@/components/ThemeToggle.vue';
import UserAvatar from '@/components/UserAvatar.vue';

const authStore = useAuthStore();
const router = useRouter();

// Sidebar state for mobile screens
const isMobileMenuOpen = ref(false);
const isUserMenuOpen = ref(false);

const comingSoon = [
  { label: 'My Files', icon: 'folder' },
  { label: 'Shared', icon: 'share' },
  { label: 'Trash', icon: 'trash' },
];

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
        <RouterLink to="/dashboard" class="flex items-center gap-2 font-bold tracking-tight text-slate-900 dark:text-white">
          <span class="grid place-items-center w-7 h-7 rounded-lg bg-violet-600 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
            </svg>
          </span>
          <span>File<span class="text-violet-600 dark:text-violet-400">Manager</span></span>
        </RouterLink>

        <!-- Close Mobile Menu Button -->
        <button @click="isMobileMenuOpen = false" class="lg:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <RouterLink
          to="/dashboard"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10Zm10 8h8v-6h-8v6Zm0-18v6h8V3h-8ZM3 21h8v-6H3v6Z" />
          </svg>
          <span>Dashboard</span>
        </RouterLink>

        <RouterLink
          to="/profile"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
          </svg>
          <span>Profile</span>
        </RouterLink>

        <p class="px-3 pt-5 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-600">
          Coming soon
        </p>

        <span
          v-for="item in comingSoon"
          :key="item.label"
          class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-600 cursor-not-allowed"
        >
          <span class="flex items-center gap-3">
            <svg v-if="item.icon === 'folder'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
            </svg>
            <svg v-else-if="item.icon === 'share'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.68 13.34a3 3 0 1 0 0-2.68m0 2.68 6.64 3.32m-6.64-6 6.64-3.32M18 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm0 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM8 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 11.2A2 2 0 0 1 14.3 20H9.7a2 2 0 0 1-2-1.8L7 7" />
            </svg>
            {{ item.label }}
          </span>
          <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800">Soon</span>
        </span>
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
          class="p-2 text-slate-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 14 5-5-5-5m5 5H9" />
          </svg>
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
          class="lg:hidden p-2 -ml-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5.5 h-5.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
          </svg>
        </button>

        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-200">
          Overview
        </h1>

        <!-- Header Actions -->
        <div class="flex items-center gap-2">
          <ThemeToggle />

          <div class="relative">
          <button
            @click="isUserMenuOpen = !isUserMenuOpen"
            class="rounded-full ring-2 ring-violet-100 dark:ring-violet-900/50 hover:ring-violet-300 dark:hover:ring-violet-700 transition"
          >
            <UserAvatar />
          </button>

          <div v-if="isUserMenuOpen" @click="isUserMenuOpen = false" class="fixed inset-0 z-40"></div>

          <div
            v-if="isUserMenuOpen"
            class="absolute right-0 mt-3 w-64 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 backdrop-blur-sm z-50 overflow-hidden"
          >
            <div class="absolute -top-2 right-4 h-4 w-4 rotate-45 border-l border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"></div>

            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <UserAvatar size="w-10 h-10" />
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                    {{ authStore.user?.name || 'User' }}
                  </p>
                  <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                    {{ authStore.user?.email || 'your@email.com' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="p-2">
              <RouterLink
                :to="{ name: 'profile' }"
                @click="isUserMenuOpen = false"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-slate-800"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-8 9a4 4 0 0 1 8 0" />
                </svg>
                Profile
              </RouterLink>

              <button
                @click="handleLogout"
                class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-950/30"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 14 5-5-5-5m5 5H9" />
                </svg>
                Log out
              </button>
            </div>
          </div>
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
