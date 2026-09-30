<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useFilesStore } from '@/stores/files'
import { useToastStore } from '@/stores/toast'
import MoveFileDialog from '@/components/MoveFileDialog.vue'
import RenameDialog from '@/components/RenameDialog.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ShareDialog from '@/components/ShareDialog.vue'
import StarButton from '@/components/StarButton.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import {
  FolderIcon,
  DocumentIcon,
  ChevronRightIcon,
  EllipsisVerticalIcon,
  ArrowsRightLeftIcon,
  PencilSquareIcon,
  ArrowDownTrayIcon,
  ShareIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const filesStore = useFilesStore()
const toastStore = useToastStore()

const movingItem = ref(null)
const renamingItem = ref(null)
const deletingItem = ref(null)
const sharingItem = ref(null)
const deleting = ref(false)

const confirmDelete = async () => {
  deleting.value = true
  try {
    const name = deletingItem.value.name
    await filesStore.deleteItem(deletingItem.value.id)
    deletingItem.value = null
    toastStore.success(`"${name}" deleted.`)
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to delete.')
  } finally {
    deleting.value = false
  }
}

const load = () => {
  filesStore.fetchFolder(route.params.id ? Number(route.params.id) : null)
}

onMounted(load)
watch(() => route.params.id, load)

const openFolder = (item) => {
  if (!item.is_folder) return
  router.push({ name: 'my-files', params: { id: item.id } })
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
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white">My Files</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Everything you've created or uploaded.
      </p>
    </div>

    <!-- Breadcrumbs -->
    <div class="flex items-center flex-wrap gap-1 text-sm">
      <RouterLink
        :to="{ name: 'my-files' }"
        :class="
          !route.params.id
            ? 'text-violet-600 dark:text-violet-400'
            : 'text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400'
        "
        class="font-medium transition"
      >
        My Files
      </RouterLink>
      <template v-for="crumb in filesStore.breadcrumbs" :key="crumb.id">
        <ChevronRightIcon class="w-3.5 h-3.5 text-slate-400 shrink-0" />
        <RouterLink
          :to="{ name: 'my-files', params: { id: crumb.id } }"
          :class="
            Number(route.params.id) === crumb.id
              ? 'text-violet-600 dark:text-violet-400'
              : 'text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400'
          "
          class="font-medium transition"
        >
          {{ crumb.name }}
        </RouterLink>
      </template>
    </div>

    <div v-if="filesStore.loading" class="text-sm text-slate-500 dark:text-slate-400">Loading…</div>

    <div
      v-else-if="filesStore.error"
      class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/50 p-4 text-sm text-red-600 dark:text-red-400"
    >
      {{ filesStore.error }}
    </div>

    <div
      v-else-if="filesStore.items.length === 0"
      class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center"
    >
      <div
        class="grid place-items-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 mx-auto mb-4"
      >
        <FolderIcon class="w-6 h-6" />
      </div>
      <h3 class="font-semibold text-slate-900 dark:text-white">Nothing here yet</h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Use "Create New" to add a folder or upload files.
      </p>
    </div>

    <div
      v-else
      class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-200 dark:divide-slate-800"
    >
      <div
        v-for="item in filesStore.items"
        :key="item.id"
        @click="openFolder(item)"
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

        <Menu as="div" class="relative shrink-0" @click.stop>
          <MenuButton
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
          >
            <EllipsisVerticalIcon class="w-5 h-5" />
          </MenuButton>
          <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <MenuItems
              class="absolute right-0 z-10 mt-2 w-40 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 divide-y divide-slate-100 dark:divide-slate-800 focus:outline-none overflow-hidden"
            >
              <div v-if="item.is_owner" class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="renamingItem = item"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                    Rename
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="movingItem = item"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <ArrowsRightLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Move
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="sharingItem = item"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <ShareIcon class="h-4 w-4" aria-hidden="true" />
                    Share
                  </button>
                </MenuItem>
              </div>
              <div v-if="!item.is_folder" class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <a
                    :href="filesStore.downloadUrl(item.id)"
                    target="_blank"
                    rel="noopener"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                    Download
                  </a>
                </MenuItem>
              </div>
              <div v-if="item.is_owner" class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="deletingItem = item"
                    :class="[
                      active ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    Delete
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>

    <MoveFileDialog
      :open="!!movingItem"
      :item="movingItem"
      @close="movingItem = null"
      @moved="movingItem = null"
    />

    <RenameDialog
      :open="!!renamingItem"
      :item="renamingItem"
      @close="renamingItem = null"
      @renamed="renamingItem = null"
    />

    <ConfirmDialog
      :open="!!deletingItem"
      title="Delete this item?"
      :message="`'${deletingItem?.name}' will be removed from your files.`"
      confirm-label="Delete"
      :loading="deleting"
      @close="deletingItem = null"
      @confirm="confirmDelete"
    />

    <ShareDialog :open="!!sharingItem" :item="sharingItem" @close="sharingItem = null" />
  </div>
</template>
