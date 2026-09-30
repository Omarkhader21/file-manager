import { defineStore } from 'pinia'
import api from '@/services/api'

export const useFilesStore = defineStore('files', {
  state: () => ({
    items: [],
    loading: false,
    error: '',
    currentFolderId: null,
    breadcrumbs: [],
    trashedItems: [],
    trashLoading: false,
    trashError: '',
    sharedItems: [],
    sharedLoading: false,
    sharedError: '',
    starredItems: [],
    starredLoading: false,
    starredError: '',
    searchResults: [],
    searchLoading: false,
    searchError: '',
    stats: { totalFiles: 0, storageUsedBytes: 0, sharedFiles: 0 },
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
    // Stops (rather than throwing) at the first ancestor we can't access —
    // e.g. browsing a folder shared with us stops at that folder, since its
    // owner's own root/parents above it are private.
    async buildBreadcrumbs(folderId) {
      const crumbs = []
      let id = folderId

      while (id) {
        let folder
        try {
          const response = await api.get(`/api/files/${id}`)
          folder = response.data.data
        } catch {
          break
        }
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
      return response.data.data
    },

    // Recreates the selected folder's directory structure (from each file's
    // webkitRelativePath) under the current folder, then uploads each file
    // into the matching subfolder. Folder creation is sequential — fine for
    // the folder sizes this app deals with; parallelize if it's ever slow.
    async uploadFolder(fileList) {
      const files = Array.from(fileList)
      if (files.length === 0) return []

      const rootParentId = this.currentFolderId
      const dirCache = new Map([['', rootParentId]])
      const topLevelFolders = []

      const resolveDir = async (dirPath) => {
        if (dirCache.has(dirPath)) return dirCache.get(dirPath)

        const segments = dirPath.split('/')
        let parentPath = ''
        let parentId = rootParentId

        for (const segment of segments) {
          const path = parentPath ? `${parentPath}/${segment}` : segment
          if (!dirCache.has(path)) {
            const response = await api.post('/api/files', { name: segment, parent_id: parentId })
            const folder = response.data.data[0]
            dirCache.set(path, folder.id)
            if (!parentPath) topLevelFolders.push(folder)
          }
          parentId = dirCache.get(path)
          parentPath = path
        }

        return parentId
      }

      const byDir = new Map()
      for (const file of files) {
        const relPath = file.webkitRelativePath || file.name
        const dir = relPath.includes('/') ? relPath.slice(0, relPath.lastIndexOf('/')) : ''
        if (!byDir.has(dir)) byDir.set(dir, [])
        byDir.get(dir).push(file)
      }

      const uploadedFiles = []
      const topLevelFiles = []
      for (const [dir, dirFiles] of byDir) {
        const parentId = await resolveDir(dir)
        const formData = new FormData()
        dirFiles.forEach((file) => formData.append('files[]', file))
        if (parentId) formData.append('parent_id', parentId)
        const response = await api.post('/api/files', formData, {
          headers: { 'Content-Type': undefined },
        })
        uploadedFiles.push(...response.data.data)
        if (!dir) topLevelFiles.push(...response.data.data)
      }

      this.items.unshift(...topLevelFolders, ...topLevelFiles)
      return [...topLevelFolders, ...uploadedFiles]
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

    async fetchTrash() {
      this.trashLoading = true
      this.trashError = ''

      try {
        const response = await api.get('/api/files/trash')
        this.trashedItems = response.data.data
      } catch (e) {
        this.trashError = e.response?.data?.message || 'Failed to load trash.'
      } finally {
        this.trashLoading = false
      }
    },

    async restoreItem(id) {
      const response = await api.post(`/api/files/${id}/restore`)
      this.trashedItems = this.trashedItems.filter((item) => item.id !== id)
      return response.data.data
    },

    async forceDeleteItem(id) {
      await api.delete(`/api/files/${id}/force`)
      this.trashedItems = this.trashedItems.filter((item) => item.id !== id)
    },

    async fetchShared() {
      this.sharedLoading = true
      this.sharedError = ''

      try {
        const response = await api.get('/api/files/shared-with-me')
        this.sharedItems = response.data.data
      } catch (e) {
        this.sharedError = e.response?.data?.message || 'Failed to load shared files.'
      } finally {
        this.sharedLoading = false
      }
    },

    async fetchShares(fileId) {
      const response = await api.get(`/api/files/${fileId}/shares`)
      return response.data.data
    },

    async shareItem(fileId, email) {
      await api.post(`/api/files/${fileId}/share`, { email })
    },

    async unshareItem(fileId, userId) {
      await api.delete(`/api/files/${fileId}/share/${userId}`)
    },

    async fetchStarred() {
      this.starredLoading = true
      this.starredError = ''

      try {
        const response = await api.get('/api/files/starred')
        this.starredItems = response.data.data
      } catch (e) {
        this.starredError = e.response?.data?.message || 'Failed to load starred files.'
      } finally {
        this.starredLoading = false
      }
    },

    // Toggles star state and keeps every list the item might currently be
    // rendered in (current folder, shared-with-me, search results, and the
    // starred list itself) in sync, without needing each view to refetch.
    async toggleStar(item) {
      const starring = !item.is_starred

      if (starring) {
        await api.post(`/api/files/${item.id}/star`)
      } else {
        await api.delete(`/api/files/${item.id}/star`)
      }

      for (const list of [this.items, this.sharedItems, this.searchResults]) {
        const match = list.find((candidate) => candidate.id === item.id)
        if (match) match.is_starred = starring
      }

      if (starring) {
        if (!this.starredItems.some((candidate) => candidate.id === item.id)) {
          this.starredItems.push({ ...item, is_starred: true })
        }
      } else {
        this.starredItems = this.starredItems.filter((candidate) => candidate.id !== item.id)
      }
    },

    async fetchStats() {
      const response = await api.get('/api/files/stats')
      this.stats = {
        totalFiles: response.data.total_files,
        storageUsedBytes: response.data.storage_used_bytes,
        sharedFiles: response.data.shared_files,
      }
    },

    async search(query) {
      this.searchQuery = query

      if (!query.trim()) {
        this.searchResults = []
        return
      }

      this.searchLoading = true
      this.searchError = ''

      try {
        const response = await api.get('/api/files/search', { params: { q: query } })
        this.searchResults = response.data.data
      } catch (e) {
        this.searchError = e.response?.data?.message || 'Search failed.'
      } finally {
        this.searchLoading = false
      }
    },
  },
})
