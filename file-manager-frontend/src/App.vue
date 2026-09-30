<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import ToastContainer from '@/components/ToastContainer.vue';

const route = useRoute();

// Use AppLayout ONLY when explicitly required, otherwise use GuestLayout
const currentLayout = computed(() => {
  return route.meta.requiresAuth ? AppLayout : GuestLayout;
});
</script>

<template>
  <component :is="currentLayout">
    <RouterView />
  </component>
  <ToastContainer />
</template>