<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Are you sure?' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Confirm' },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])

const close = (loading) => {
  if (loading) return
  emit('close')
}
</script>

<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" class="relative z-50" @close="close(loading)">
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
              <div class="flex items-center gap-3 mb-2">
                <span
                  class="grid place-items-center w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 shrink-0"
                >
                  <ExclamationTriangleIcon class="w-5 h-5" />
                </span>
                <DialogTitle class="text-lg font-semibold text-slate-900 dark:text-white">
                  {{ title }}
                </DialogTitle>
              </div>
              <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ message }}</p>

              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  @click="close(loading)"
                  class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  :disabled="loading"
                  @click="emit('confirm')"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg shadow-sm shadow-red-600/20 transition"
                >
                  {{ loading ? 'Working…' : confirmLabel }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
