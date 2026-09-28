<script setup>
import { useAuthStore } from '@/stores/auth';

defineProps({
  size: { type: String, default: 'w-9 h-9' },
  textSize: { type: String, default: 'text-sm' },
});

const authStore = useAuthStore();
</script>

<template>
  <div
    :class="[
      'rounded-full overflow-hidden flex items-center justify-center font-semibold shrink-0',
      authStore.user?.profile_photo_url ? 'bg-slate-100 dark:bg-slate-800' : 'bg-violet-600 text-white',
      size,
      textSize,
    ]"
  >
    <img
      v-if="authStore.user?.profile_photo_url"
      :src="authStore.user.profile_photo_url"
      alt=""
      class="w-full h-full object-cover"
    />
    <span v-else>{{ authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}</span>
  </div>
</template>
