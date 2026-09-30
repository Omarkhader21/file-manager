<script setup>
import { ref, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { FolderIcon, ChevronRightIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'
import { useFilesStore } from '@/stores/files'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  open: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close', 'moved'])

const filesStore = useFilesStore()
const toastStore = useToastStore()

const browseFolderId = ref(null)
const breadcrumbs = ref([])
const folders = ref([])
const loading = ref(false)
const moving = ref(false)
const error = ref('')

async function loadFolder(folderId) {
  loading.value = true
  error.value = ''
  browseFolderId.value = folderId

  try {
    const [listResponse, crumbs] = await Promise.all([
      api.get('/api/files', { params: folderId ? { parent_id: folderId } : {} }),
      filesStore.buildBreadcrumbs(folderId),
    ])
    folders.value = listResponse.data.data.filter(
      (folder) => folder.is_folder && folder.id !== props.item?.id,
    )
    breadcrumbs.value = crumbs
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load folders.'
  } finally {
    loading.value = false
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) loadFolder(null)
  },
)

const close = () => {
  if (moving.value) return
  emit('close')
}

const moveHere = async () => {
  moving.value = true
  error.value = ''

  try {
    const destination = breadcrumbs.value.at(-1)?.name ?? 'My Files'
    const moved = await filesStore.moveItem(props.item.id, browseFolderId.value)
    emit('moved', moved)
    toastStore.success(`Moved "${moved.name}" to "${destination}".`)
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to move.'
  } finally {
    moving.value = false
  }
}
</script>

<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild
        as="template"
        enter="duration-150 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-100 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-150 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-100 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 p-6"
            >
              <div class="flex items-center gap-3 mb-1">
                <span
                  class="grid place-items-center w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 shrink-0"
                >
                  <ArrowsRightLeftIcon class="w-5 h-5" />
                </span>
                <DialogTitle class="text-lg font-semibold text-slate-900 dark:text-white truncate">
                  Move "{{ item?.name }}"
                </DialogTitle>
              </div>
              <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Choose a destination folder.</p>

              <div class="flex items-center flex-wrap gap-1 text-sm mb-3">
                <button
                  type="button"
                  @click="loadFolder(null)"
                  :class="
                    browseFolderId === null
                      ? 'text-violet-600 dark:text-violet-400'
                      : 'text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400'
                  "
                  class="font-medium transition"
                >
                  My Files
                </button>
                <template v-for="crumb in breadcrumbs" :key="crumb.id">
                  <ChevronRightIcon class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <button
                    type="button"
                    @click="loadFolder(crumb.id)"
                    :class="
                      browseFolderId === crumb.id
                        ? 'text-violet-600 dark:text-violet-400'
                        : 'text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400'
                    "
                    class="font-medium transition"
                  >
                    {{ crumb.name }}
                  </button>
                </template>
              </div>

              <div class="h-56 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <div v-if="loading" class="p-4 text-sm text-slate-500 dark:text-slate-400">Loading…</div>
                <div v-else-if="folders.length === 0" class="p-4 text-sm text-slate-500 dark:text-slate-400">
                  No subfolders here.
                </div>
                <template v-else>
                  <button
                    v-for="folder in folders"
                    :key="folder.id"
                    type="button"
                    @click="loadFolder(folder.id)"
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                  >
                    <FolderIcon class="w-4.5 h-4.5 text-violet-500 shrink-0" />
                    <span class="truncate">{{ folder.name }}</span>
                  </button>
                </template>
              </div>

              <p v-if="error" class="mt-2 text-xs text-red-500">{{ error }}</p>

              <div class="mt-6 flex justify-end gap-2">
                <button
                  type="button"
                  @click="close"
                  class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  :disabled="moving"
                  @click="moveHere"
                  class="px-4 py-2 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg shadow-sm shadow-violet-600/20 transition"
                >
                  {{ moving ? 'Moving…' : 'Move Here' }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
