import { defineStore } from 'pinia'

const getInitialTheme = () => {
  const saved = localStorage.getItem('theme')
  if (saved === 'light' || saved === 'dark') return saved
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    theme: getInitialTheme(),
  }),

  actions: {
    apply() {
      document.documentElement.classList.toggle('dark', this.theme === 'dark')
    },

    init() {
      this.apply()
    },

    toggle() {
      this.theme = this.theme === 'dark' ? 'light' : 'dark'
      localStorage.setItem('theme', this.theme)
      this.apply()
    },
  },
})
