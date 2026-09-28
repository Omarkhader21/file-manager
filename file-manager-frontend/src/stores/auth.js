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

    async updateProfile(payload) {
      const response = await api.patch('/api/profile', payload)
      this.user = response.data
    },

    async updatePassword(payload) {
      await api.put('/api/profile/password', payload)
    },

    async updatePhoto(file) {
      const formData = new FormData()
      formData.append('photo', file)
      const response = await api.post('/api/profile/photo', formData, {
        headers: { 'Content-Type': undefined },
      })
      this.user = response.data
    },

    async removePhoto() {
      const response = await api.delete('/api/profile/photo')
      this.user = response.data
    },
  },
})
