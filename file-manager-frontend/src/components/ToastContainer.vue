<script setup>
import { useToastStore } from '@/stores/toast'
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const toastStore = useToastStore()
</script>

<template>
  <div class="fixed top-4 right-4 z-100 flex flex-col gap-2 w-full max-w-sm pointer-events-none">
    <TransitionGroup
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-x-4"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition duration-150 ease-in absolute"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
      move-class="transition duration-200"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 rounded-xl border p-3.5 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 bg-white dark:bg-slate-900"
        :class="
          toast.type === 'error'
            ? 'border-red-200 dark:border-red-900/50'
            : 'border-slate-200 dark:border-slate-800'
        "
      >
        <CheckCircleIcon
          v-if="toast.type === 'success'"
          class="w-5 h-5 text-violet-600 dark:text-violet-400 shrink-0 mt-0.5"
        />
        <XCircleIcon v-else class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
        <p class="flex-1 text-sm text-slate-700 dark:text-slate-300">{{ toast.message }}</p>
        <button
          @click="toastStore.remove(toast.id)"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition shrink-0"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
