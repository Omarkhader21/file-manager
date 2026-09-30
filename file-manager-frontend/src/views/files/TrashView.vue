<script setup>
import { ref, onMounted } from 'vue'
import { useFilesStore } from '@/stores/files'
import { useToastStore } from '@/stores/toast'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import {
  FolderIcon,
  DocumentIcon,
  TrashIcon,
  EllipsisVerticalIcon,
  ArrowUturnLeftIcon,
} from '@heroicons/vue/24/outline'

const filesStore = useFilesStore()
const toastStore = useToastStore()

const forceDeletingItem = ref(null)
const forceDeleting = ref(false)

onMounted(() => {
  filesStore.fetchTrash()
})

const restore = async (item) => {
  try {
    await filesStore.restoreItem(item.id)
    toastStore.success(`"${item.name}" restored.`)
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to restore.')
  }
}

const confirmForceDelete = async () => {
  forceDeleting.value = true
  try {
    const name = forceDeletingItem.value.name
    await filesStore.forceDeleteItem(forceDeletingItem.value.id)
    forceDeletingItem.value = null
    toastStore.success(`"${name}" permanently deleted.`)
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to delete.')
  } finally {
    forceDeleting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Trash</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Deleted items stay here until you restore or permanently delete them.
      </p>
    </div>

    <div v-if="filesStore.trashLoading" class="text-sm text-slate-500 dark:text-slate-400">
      Loading…
    </div>

    <div
      v-else-if="filesStore.trashError"
      class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/50 p-4 text-sm text-red-600 dark:text-red-400"
    >
      {{ filesStore.trashError }}
    </div>

    <div
      v-else-if="filesStore.trashedItems.length === 0"
      class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center"
    >
      <div
        class="grid place-items-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 mx-auto mb-4"
      >
        <TrashIcon class="w-6 h-6" />
      </div>
      <h3 class="font-semibold text-slate-900 dark:text-white">Trash is empty</h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Items you delete from My Files show up here.
      </p>
    </div>

    <div
      v-else
      class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-200 dark:divide-slate-800"
    >
      <div
        v-for="item in filesStore.trashedItems"
        :key="item.id"
        class="flex items-center gap-3 px-4 py-3 first:rounded-t-2xl last:rounded-b-2xl"
      >
        <span
          class="grid place-items-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 shrink-0"
        >
          <FolderIcon v-if="item.is_folder" class="w-5 h-5" />
          <DocumentIcon v-else class="w-5 h-5" />
        </span>
        <p class="min-w-0 flex-1 text-sm font-medium text-slate-900 dark:text-white truncate">
          {{ item.name }}
        </p>

        <Menu as="div" class="relative shrink-0">
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
              class="absolute right-0 z-10 mt-2 w-48 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 divide-y divide-slate-100 dark:divide-slate-800 focus:outline-none overflow-hidden"
            >
              <div class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="restore(item)"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <ArrowUturnLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Restore
                  </button>
                </MenuItem>
              </div>
              <div class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="forceDeletingItem = item"
                    :class="[
                      active ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                    ]"
                  >
                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    Delete Forever
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>

    <ConfirmDialog
      :open="!!forceDeletingItem"
      title="Delete forever?"
      :message="`'${forceDeletingItem?.name}' will be permanently deleted. This cannot be undone.`"
      confirm-label="Delete Forever"
      :loading="forceDeleting"
      @close="forceDeletingItem = null"
      @confirm="confirmForceDelete"
    />
  </div>
</template>
