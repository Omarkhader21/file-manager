<script setup>
import { computed, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import UserAvatar from '@/components/UserAvatar.vue';

const authStore = useAuthStore();

const photoInput = ref(null);
const isUploadingPhoto = ref(false);
const photoError = ref('');

const selectPhoto = () => photoInput.value?.click();

const handlePhotoChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  isUploadingPhoto.value = true;
  photoError.value = '';

  try {
    await authStore.updatePhoto(file);
  } catch (error) {
    photoError.value = error.response?.data?.errors?.photo?.[0] || 'Failed to upload photo.';
  } finally {
    isUploadingPhoto.value = false;
    event.target.value = '';
  }
};

const removePhoto = async () => {
  isUploadingPhoto.value = true;
  photoError.value = '';

  try {
    await authStore.removePhoto();
  } catch {
    photoError.value = 'Failed to remove photo.';
  } finally {
    isUploadingPhoto.value = false;
  }
};

const tabs = [
  { id: 'general', label: 'General Information' },
  { id: 'password', label: 'Update Password' },
];

const activeTab = ref('general');

const generalForm = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const isSaving = ref(false);
const isUpdatingPassword = ref(false);
const generalErrors = ref({});
const passwordErrors = ref({});
const generalMessage = ref('');
const passwordMessage = ref('');

const profileCompletion = computed(() => {
  const fields = [generalForm.value.name, generalForm.value.email];
  const filled = fields.filter((field) => field && field.trim().length > 0).length;
  return Math.round((filled / fields.length) * 100);
});

const saveProfile = async () => {
  isSaving.value = true;
  generalErrors.value = {};
  generalMessage.value = '';

  try {
    await authStore.updateProfile(generalForm.value);
    generalMessage.value = 'Profile updated.';
  } catch (error) {
    if (error.response?.status === 422) {
      generalErrors.value = error.response.data.errors || {};
    } else {
      generalMessage.value = error.response?.data?.message || 'Failed to update profile.';
    }
  } finally {
    isSaving.value = false;
  }
};

const updatePassword = async () => {
  isUpdatingPassword.value = true;
  passwordErrors.value = {};
  passwordMessage.value = '';

  try {
    await authStore.updatePassword(passwordForm.value);
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
    passwordMessage.value = 'Password updated.';
  } catch (error) {
    if (error.response?.status === 422) {
      passwordErrors.value = error.response.data.errors || {};
    } else {
      passwordMessage.value = error.response?.data?.message || 'Failed to update password.';
    }
  } finally {
    isUpdatingPassword.value = false;
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-violet-600 dark:text-violet-400">
          Account
        </p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
          Profile settings
        </h1>
      </div>

      <div class="rounded-full border border-violet-200 bg-violet-50 px-3 py-1.5 text-sm font-medium text-violet-700 dark:border-violet-900/60 dark:bg-violet-950/40 dark:text-violet-300">
        {{ profileCompletion }}% complete
      </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
      <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
        <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
          <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              @click="activeTab = tab.id"
              :class="[
                'rounded-lg px-4 py-2 text-sm font-medium transition',
                activeTab === tab.id
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                  : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
              ]"
            >
              {{ tab.label }}
            </button>
          </div>
        </div>

        <div class="pt-6">
          <form v-if="activeTab === 'general'" @submit.prevent="saveProfile" class="space-y-6">
            <div
              v-if="generalMessage"
              class="rounded-lg border p-3 text-sm"
              :class="Object.keys(generalErrors).length
                ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-900/50 dark:bg-red-950/50 dark:text-red-400'
                : 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-900/50 dark:bg-emerald-950/50 dark:text-emerald-400'"
            >
              {{ generalMessage }}
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <label class="block space-y-2 md:col-span-1">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Full name</span>
                <input
                  v-model="generalForm.name"
                  type="text"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-950"
                  placeholder="Your name"
                />
                <p v-if="generalErrors.name" class="text-xs text-red-500">{{ generalErrors.name[0] }}</p>
              </label>

              <label class="block space-y-2 md:col-span-1">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Email</span>
                <input
                  v-model="generalForm.email"
                  type="email"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-950"
                  placeholder="you@example.com"
                />
                <p v-if="generalErrors.email" class="text-xs text-red-500">{{ generalErrors.email[0] }}</p>
              </label>
            </div>

            <div class="flex items-center justify-end pt-2">
              <button
                type="submit"
                :disabled="isSaving"
                class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-70"
              >
                {{ isSaving ? 'Saving...' : 'Save changes' }}
              </button>
            </div>
          </form>

          <form v-else @submit.prevent="updatePassword" class="space-y-6">
            <div
              v-if="passwordMessage"
              class="rounded-lg border p-3 text-sm"
              :class="Object.keys(passwordErrors).length
                ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-900/50 dark:bg-red-950/50 dark:text-red-400'
                : 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-900/50 dark:bg-emerald-950/50 dark:text-emerald-400'"
            >
              {{ passwordMessage }}
            </div>

            <div class="space-y-5">
              <label class="block space-y-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Current password</span>
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-950"
                  placeholder="Enter current password"
                />
                <p v-if="passwordErrors.current_password" class="text-xs text-red-500">{{ passwordErrors.current_password[0] }}</p>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">New password</span>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-950"
                  placeholder="At least 8 characters"
                />
                <p v-if="passwordErrors.password" class="text-xs text-red-500">{{ passwordErrors.password[0] }}</p>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Confirm new password</span>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-950"
                  placeholder="Re-enter new password"
                />
              </label>
            </div>

            <div class="flex items-center justify-end pt-2">
              <button
                type="submit"
                :disabled="isUpdatingPassword"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-violet-600 dark:hover:bg-violet-500"
              >
                {{ isUpdatingPassword ? 'Updating...' : 'Update password' }}
              </button>
            </div>
          </form>
        </div>
      </section>

      <aside class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-4">
          <UserAvatar size="w-16 h-16" textSize="text-xl" />
          <div>
            <p class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ authStore.user?.name || 'User' }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {{ authStore.user?.email || 'No email available' }}
            </p>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
          <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
          <button
            type="button"
            :disabled="isUploadingPhoto"
            @click="selectPhoto"
            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-70 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          >
            {{ isUploadingPhoto ? 'Uploading...' : 'Change photo' }}
          </button>
          <button
            v-if="authStore.user?.profile_photo"
            type="button"
            :disabled="isUploadingPhoto"
            @click="removePhoto"
            class="text-xs font-medium text-red-600 transition hover:underline disabled:cursor-not-allowed disabled:opacity-70 dark:text-red-400"
          >
            Remove
          </button>
        </div>
        <p v-if="photoError" class="mt-2 text-xs text-red-500">{{ photoError }}</p>

        <div class="mt-6 space-y-4">
          <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/60">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
              Status
            </p>
            <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
              Active account
            </p>
          </div>

          <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/60">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
              Last updated
            </p>
            <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
              Today
            </p>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>
