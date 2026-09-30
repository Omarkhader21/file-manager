<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { FolderIcon, EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.value)
    router.push({ name: 'dashboard' })
  } catch (error) {
    // Handle error message from Laravel response
    errorMessage.value = error.response?.data?.message || 'Invalid email or password.'
    form.value.password = ''
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[calc(100vh-8rem)] flex items-center justify-center p-4">
    <div
      class="w-full max-w-md bg-white dark:bg-slate-900 shadow-xl shadow-slate-900/5 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-8"
    >
      <div class="grid place-items-center w-11 h-11 rounded-xl bg-violet-600 text-white mx-auto mb-5">
        <FolderIcon class="w-5.5 h-5.5" />
      </div>

      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center">
        Sign in to your account
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 text-center">
        Enter your credentials below to access your dashboard.
      </p>

      <!-- Error Alert -->
      <div
        v-if="errorMessage"
        class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 text-sm rounded-lg"
      >
        {{ errorMessage }}
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Email Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Email Address
          </label>
          <div class="relative">
            <EnvelopeIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full pl-10.5 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
            />
          </div>
        </div>

        <!-- Password Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Password
          </label>
          <div class="relative">
            <LockClosedIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full pl-10.5 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full mt-2 py-2.5 px-4 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white font-medium text-sm rounded-lg shadow-sm shadow-violet-600/20 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 transition flex justify-center items-center gap-2 cursor-pointer"
        >
          <span
            v-if="loading"
            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
          ></span>
          <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
        </button>
      </form>

      <!-- Footer Switch Link -->
      <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
        Don't have an account?
        <RouterLink
          to="/register"
          class="font-medium text-violet-600 dark:text-violet-400 hover:underline"
        >
          Create one now
        </RouterLink>
      </p>
    </div>
  </div>
</template>
