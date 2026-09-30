<script setup>
import { ref, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { PencilSquareIcon } from '@heroicons/vue/24/outline'
import { useFilesStore } from '@/stores/files'

const props = defineProps({
  open: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close', 'renamed'])

const filesStore = useFilesStore()

const name = ref('')
const nameInput = ref(null)
const loading = ref(false)
const error = ref('')

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) name.value = props.item?.name ?? ''
  },
)

const close = () => {
  if (loading.value) return
  error.value = ''
  emit('close')
}

const submit = async () => {
  if (!name.value.trim()) return

  loading.value = true
  error.value = ''

  try {
    const renamed = await filesStore.renameItem(props.item.id, name.value.trim())
    emit('renamed', renamed)
    emit('close')
  } catch (e) {
    error.value =
      e.response?.data?.errors?.name?.[0] || e.response?.data?.message || 'Failed to rename.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" class="relative z-50" :initial-focus="nameInput" @close="close">
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
              class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 p-6"
            >
              <div class="flex items-center gap-3 mb-4">
                <span
                  class="grid place-items-center w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400"
                >
                  <PencilSquareIcon class="w-5 h-5" />
                </span>
                <DialogTitle class="text-lg font-semibold text-slate-900 dark:text-white">
                  Rename
                </DialogTitle>
              </div>

              <form @submit.prevent="submit">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                  Name
                </label>
                <input
                  ref="nameInput"
                  v-model="name"
                  type="text"
                  class="w-full px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
                />
                <p v-if="error" class="mt-1.5 text-xs text-red-500">{{ error }}</p>

                <div class="mt-6 flex justify-end gap-2">
                  <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="loading || !name.trim()"
                    class="px-4 py-2 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg shadow-sm shadow-violet-600/20 transition"
                  >
                    {{ loading ? 'Saving…' : 'Save' }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
