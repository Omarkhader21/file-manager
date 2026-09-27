import { fileURLToPath, URL } from 'node:url'
import fs from 'node:fs'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    allowedHosts: ['app.file-manager-backend.test'],
    https: {
      key: fs.readFileSync(new URL('./.cert/app.file-manager-backend.test-key.pem', import.meta.url)),
      cert: fs.readFileSync(new URL('./.cert/app.file-manager-backend.test.pem', import.meta.url)),
    },
  },
})
