import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
  },

  actions: {
    async csrf() {
      await api.get('/sanctum/csrf-cookie')
    },

    async register(credentials) {
      await this.csrf()
      await api.post('/register', credentials)
      await this.fetchUser()
    },

    async login(credentials) {
      await this.csrf()
      await api.post('/login', credentials)
      await this.fetchUser()
    },

    async logout() {
      await api.post('/logout')
      this.user = null
    },

    async fetchUser() {
      try {
        const response = await api.get('/api/user')
        this.user = response.data
      } catch {
        this.user = null
      }
    },
  },
})
