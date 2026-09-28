<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const errorMessage = ref('')
const loading = ref(false)

const handleRegister = async () => {
  // Client-side quick password match validation
  if (form.value.password !== form.value.password_confirmation) {
    errors.value = { password: ['Passwords do not match.'] }
    return
  }

  loading.value = true
  errors.value = {}
  errorMessage.value = ''

  try {
    await authStore.register(form.value)
    router.push({ name: 'dashboard' })
  } catch (error) {
    if (error.response?.status === 422) {
      // Catch Laravel field validation errors
      errors.value = error.response.data.errors || {}
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to register. Please try again.'
    }
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
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5.5 h-5.5">
          <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
        </svg>
      </div>

      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center">
        Create an account
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 text-center">
        Get started with your free account today.
      </p>

      <!-- Generic Error Alert -->
      <div
        v-if="errorMessage"
        class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 text-sm rounded-lg"
      >
        {{ errorMessage }}
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Full Name Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Full Name
          </label>
          <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
            </svg>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="John Doe"
              class="w-full pl-10.5 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
            />
          </div>
          <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name[0] }}</p>
        </div>

        <!-- Email Address Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Email Address
          </label>
          <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            </svg>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full pl-10.5 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
            />
          </div>
          <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email[0] }}</p>
        </div>

        <!-- Password Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Password
          </label>
          <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 1 0-9 0v3m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v7a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4.5 19v-7a1.5 1.5 0 0 1 1.5-1.5Z" />
            </svg>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full pl-10.5 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
            />
          </div>
          <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password[0] }}</p>
        </div>

        <!-- Confirm Password Input -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            Confirm Password
          </label>
          <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 1 0-9 0v3m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v7a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4.5 19v-7a1.5 1.5 0 0 1 1.5-1.5Z" />
            </svg>
            <input
              v-model="form.password_confirmation"
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
          <span>{{ loading ? 'Creating account...' : 'Create Account' }}</span>
        </button>
      </form>

      <!-- Footer Switch Link -->
      <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
        Already have an account?
        <RouterLink
          to="/login"
          class="font-medium text-violet-600 dark:text-violet-400 hover:underline"
        >
          Sign in
        </RouterLink>
      </p>
    </div>
  </div>
</template>
