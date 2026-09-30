<script setup>
import { ref, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { ShareIcon, XMarkIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { useFilesStore } from '@/stores/files'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  open: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const filesStore = useFilesStore()
const toastStore = useToastStore()

const shares = ref([])
const loadingShares = ref(false)
const email = ref('')
const sharing = ref(false)
const error = ref('')

watch(
  () => props.open,
  async (isOpen) => {
    if (!isOpen) return
    email.value = ''
    error.value = ''
    loadingShares.value = true
    try {
      shares.value = await filesStore.fetchShares(props.item.id)
    } catch {
      shares.value = []
    } finally {
      loadingShares.value = false
    }
  },
)

const close = () => {
  if (sharing.value) return
  emit('close')
}

const submit = async () => {
  if (!email.value.trim()) return

  sharing.value = true
  error.value = ''

  try {
    await filesStore.shareItem(props.item.id, email.value.trim())
    shares.value = await filesStore.fetchShares(props.item.id)
    toastStore.success(`Shared "${props.item.name}" with ${email.value.trim()}.`)
    email.value = ''
  } catch (e) {
    error.value =
      e.response?.data?.errors?.email?.[0] || e.response?.data?.message || 'Failed to share.'
  } finally {
    sharing.value = false
  }
}

const unshare = async (user) => {
  try {
    await filesStore.unshareItem(props.item.id, user.id)
    shares.value = shares.value.filter((share) => share.id !== user.id)
    toastStore.success(`Removed ${user.email} from "${props.item.name}".`)
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to remove.')
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
              class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 p-6"
            >
              <div class="flex items-center gap-3 mb-4">
                <span
                  class="grid place-items-center w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 shrink-0"
                >
                  <ShareIcon class="w-5 h-5" />
                </span>
                <DialogTitle class="text-lg font-semibold text-slate-900 dark:text-white truncate">
                  Share "{{ item?.name }}"
                </DialogTitle>
              </div>

              <form @submit.prevent="submit" class="flex gap-2">
                <input
                  v-model="email"
                  type="email"
                  placeholder="person@example.com"
                  class="flex-1 min-w-0 px-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition"
                />
                <button
                  type="submit"
                  :disabled="sharing || !email.trim()"
                  class="px-4 py-2 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg shadow-sm shadow-violet-600/20 transition shrink-0"
                >
                  {{ sharing ? 'Sharing…' : 'Share' }}
                </button>
              </form>
              <p v-if="error" class="mt-1.5 text-xs text-red-500">{{ error }}</p>

              <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-600 mb-2">
                  People with access
                </p>
                <p v-if="loadingShares" class="text-sm text-slate-500 dark:text-slate-400">Loading…</p>
                <p v-else-if="shares.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
                  Only you, so far.
                </p>
                <ul v-else class="space-y-1.5 max-h-48 overflow-y-auto">
                  <li
                    v-for="user in shares"
                    :key="user.id"
                    class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800"
                  >
                    <UserCircleIcon class="w-7 h-7 text-slate-400 shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                        {{ user.name }}
                      </p>
                      <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ user.email }}</p>
                    </div>
                    <button
                      type="button"
                      @click="unshare(user)"
                      title="Remove access"
                      class="p-1 text-slate-400 hover:text-red-600 dark:hover:text-red-400 rounded transition shrink-0"
                    >
                      <XMarkIcon class="w-4 h-4" />
                    </button>
                  </li>
                </ul>
              </div>

              <div class="mt-6 flex justify-end">
                <button
                  type="button"
                  @click="close"
                  class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                  Done
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
