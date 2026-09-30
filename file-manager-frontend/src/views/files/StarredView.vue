<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFilesStore } from '@/stores/files'
import StarButton from '@/components/StarButton.vue'
import { FolderIcon, DocumentIcon, ArrowDownTrayIcon, StarIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const filesStore = useFilesStore()

onMounted(() => {
  filesStore.fetchStarred()
})

const openItem = (item) => {
  if (item.is_folder) {
    router.push({ name: 'my-files', params: { id: item.id } })
  }
}

function formatSize(bytes) {
  if (!bytes) return ''
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unit = 0
  while (size >= 1024 && unit < units.length - 1) {
    size /= 1024
    unit++
  }
  return `${size.toFixed(unit > 0 ? 1 : 0)} ${units[unit]}`
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Starred</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Files and folders you've starred for quick access.
      </p>
    </div>

    <div v-if="filesStore.starredLoading" class="text-sm text-slate-500 dark:text-slate-400">
      Loading…
    </div>

    <div
      v-else-if="filesStore.starredError"
      class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/50 p-4 text-sm text-red-600 dark:text-red-400"
    >
      {{ filesStore.starredError }}
    </div>

    <div
      v-else-if="filesStore.starredItems.length === 0"
      class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center"
    >
      <div
        class="grid place-items-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 mx-auto mb-4"
      >
        <StarIcon class="w-6 h-6" />
      </div>
      <h3 class="font-semibold text-slate-900 dark:text-white">Nothing starred yet</h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Star a file or folder to find it here quickly.
      </p>
    </div>

    <div
      v-else
      class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-200 dark:divide-slate-800"
    >
      <div
        v-for="item in filesStore.starredItems"
        :key="item.id"
        @click="openItem(item)"
        :class="item.is_folder ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60' : ''"
        class="flex items-center gap-3 px-4 py-3 transition first:rounded-t-2xl last:rounded-b-2xl"
      >
        <span
          class="grid place-items-center w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 shrink-0"
        >
          <FolderIcon v-if="item.is_folder" class="w-5 h-5" />
          <DocumentIcon v-else class="w-5 h-5" />
        </span>
        <p class="min-w-0 flex-1 text-sm font-medium text-slate-900 dark:text-white truncate">
          {{ item.name }}
        </p>
        <span v-if="!item.is_folder" class="text-xs text-slate-500 dark:text-slate-400 shrink-0">
          {{ formatSize(item.size) }}
        </span>
        <StarButton :item="item" />
        <a
          v-if="!item.is_folder"
          :href="filesStore.downloadUrl(item.id)"
          target="_blank"
          rel="noopener"
          @click.stop
          title="Download"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition shrink-0"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </a>
      </div>
    </div>
  </div>
</template>
