import { defineStore } from 'pinia'
import api from '@/services/api'

export const useFilesStore = defineStore('files', {
  state: () => ({
    items: [],
    loading: false,
    error: '',
    currentFolderId: null,
    breadcrumbs: [],
  }),

  actions: {
    async fetchFolder(folderId = null) {
      this.loading = true
      this.error = ''
      this.currentFolderId = folderId

      try {
        const [listResponse, crumbs] = await Promise.all([
          api.get('/api/files', { params: folderId ? { parent_id: folderId } : {} }),
          this.buildBreadcrumbs(folderId),
        ])
        this.items = listResponse.data.data
        this.breadcrumbs = crumbs
      } catch (e) {
        this.error = e.response?.data?.message || 'Failed to load files.'
      } finally {
        this.loading = false
      }
    },

    // Walks parent_id up to the (invisible) root folder, collecting names along
    // the way. A few sequential requests for a deeply nested folder, but the
    // app doesn't have deep trees yet and this needs no new backend endpoint.
    async buildBreadcrumbs(folderId) {
      const crumbs = []
      let id = folderId

      while (id) {
        const response = await api.get(`/api/files/${id}`)
        const folder = response.data.data
        if (folder.parent_id === null) break
        crumbs.unshift({ id: folder.id, name: folder.name })
        id = folder.parent_id
      }

      return crumbs
    },

    addItem(file) {
      this.items.unshift(file)
    },

    removeItem(id) {
      this.items = this.items.filter((item) => item.id !== id)
    },

    async uploadFiles(fileList) {
      const formData = new FormData()
      Array.from(fileList).forEach((file) => {
        formData.append('files[]', file)
      })
      if (this.currentFolderId) {
        formData.append('parent_id', this.currentFolderId)
      }

      const response = await api.post('/api/files', formData, {
        headers: { 'Content-Type': undefined },
      })

      this.items.unshift(...response.data.data)
    },

    async moveItem(id, parentId) {
      const response = await api.put(`/api/files/${id}`, { parent_id: parentId })
      this.removeItem(id)
      return response.data.data
    },

    async renameItem(id, name) {
      const response = await api.put(`/api/files/${id}`, { name })
      const index = this.items.findIndex((item) => item.id === id)
      if (index !== -1) this.items[index] = response.data.data
      return response.data.data
    },

    async deleteItem(id) {
      await api.delete(`/api/files/${id}`)
      this.removeItem(id)
    },

    downloadUrl(id) {
      return api.getUri({ url: `/api/files/${id}/download` })
    },
  },
})
