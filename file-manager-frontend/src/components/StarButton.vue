<script setup>
import { StarIcon as StarIconOutline } from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'
import { useFilesStore } from '@/stores/files'

const props = defineProps({
  item: { type: Object, required: true },
})

const filesStore = useFilesStore()

const toggle = () => {
  filesStore.toggleStar(props.item)
}
</script>

<template>
  <button
    type="button"
    @click.stop="toggle"
    :title="item.is_starred ? 'Unstar' : 'Star'"
    class="p-1.5 rounded-lg transition shrink-0"
    :class="
      item.is_starred
        ? 'text-amber-400 hover:text-amber-500'
        : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700'
    "
  >
    <StarIconSolid v-if="item.is_starred" class="w-5 h-5" />
    <StarIconOutline v-else class="w-5 h-5" />
  </button>
</template>
