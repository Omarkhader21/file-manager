<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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
      class="w-full max-w-md bg-white dark:bg-slate-800 shadow-xl rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-8"
    >
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
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="you@example.com"
            class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
          />
        </div>

        <!-- Password Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Password
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
          />
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full mt-2 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-medium text-sm rounded-lg shadow-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500/50 transition flex justify-center items-center gap-2 cursor-pointer"
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
          class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
        >
          Create one now
        </RouterLink>
      </p>
    </div>
  </div>
</template>
