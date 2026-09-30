import { defineStore } from 'pinia'

let nextId = 1

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [],
  }),

  actions: {
    push(type, message, duration = 4000) {
      const id = nextId++
      this.toasts.push({ id, type, message })
      setTimeout(() => this.remove(id), duration)
    },

    success(message) {
      this.push('success', message)
    },

    error(message) {
      this.push('error', message)
    },

    remove(id) {
      this.toasts = this.toasts.filter((toast) => toast.id !== id)
    },
  },
})
