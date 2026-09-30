<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFilesStore } from '@/stores/files'
import { useToastStore } from '@/stores/toast'
import ThemeToggle from '@/components/ThemeToggle.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import NewFolderDialog from '@/components/NewFolderDialog.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'
import {
  PlusIcon,
  FolderPlusIcon,
  ArrowUpTrayIcon,
  FolderIcon,
  XMarkIcon,
  Squares2X2Icon,
  UserIcon,
  ShareIcon,
  StarIcon,
  TrashIcon,
  Bars3Icon,
  ArrowLeftStartOnRectangleIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const filesStore = useFilesStore()
const toastStore = useToastStore()
const router = useRouter()

// Header search box — pushes to the dedicated search route as the user types.
const searchQuery = ref('')
let searchDebounce = null
const onSearchInput = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    const q = searchQuery.value.trim()
    if (q) router.push({ name: 'search', query: { q } })
  }, 300)
}

// Sidebar state for mobile screens
const isMobileMenuOpen = ref(false)
const isUserMenuOpen = ref(false)

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

// "Create new" dropdown actions.
const fileInput = ref(null)
const folderInput = ref(null)
const isNewFolderOpen = ref(false)
const isUploading = ref(false)

const triggerFileUpload = () => fileInput.value?.click()
const triggerFolderUpload = () => folderInput.value?.click()

const handleFolderCreated = (folder) => {
  filesStore.addItem(folder)
}

const handleFilesSelected = async (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  isUploading.value = true
  try {
    const uploaded = await filesStore.uploadFiles(files)
    toastStore.success(
      uploaded.length === 1 ? `"${uploaded[0].name}" uploaded.` : `${uploaded.length} files uploaded.`,
    )
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to upload.')
  } finally {
    isUploading.value = false
    event.target.value = ''
  }
}

const handleFolderSelected = async (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  isUploading.value = true
  try {
    const uploaded = await filesStore.uploadFolder(files)
    const topName = files[0].webkitRelativePath.split('/')[0]
    toastStore.success(`"${topName}" uploaded (${uploaded.length} items).`)
  } catch (e) {
    toastStore.error(e.response?.data?.message || 'Failed to upload folder.')
  } finally {
    isUploading.value = false
    event.target.value = ''
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 flex">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div
      v-if="isMobileMenuOpen"
      @click="isMobileMenuOpen = false"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden"
    ></div>

    <!-- Sidebar Navigation -->
    <aside
      :class="[
        'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-200 ease-in-out',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- App Brand Logo -->
      <div
        class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800"
      >
        <RouterLink
          to="/dashboard"
          class="flex items-center gap-2 font-bold tracking-tight text-slate-900 dark:text-white"
        >
          <span class="grid place-items-center w-7 h-7 rounded-lg bg-violet-600 text-white">
            <FolderIcon class="w-4 h-4" />
          </span>
          <span>File<span class="text-violet-600 dark:text-violet-400">Manager</span></span>
        </RouterLink>

        <!-- Close Mobile Menu Button -->
        <button
          @click="isMobileMenuOpen = false"
          class="lg:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
        >
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <Menu as="div" class="relative mb-4">
          <MenuButton
            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-violet-600 hover:bg-violet-700 px-3 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/50"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            Create New
            <ChevronDownIcon class="h-4 w-4 ml-auto" aria-hidden="true" />
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
              class="absolute left-0 z-50 mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 focus:outline-none overflow-hidden"
            >
              <div class="p-1.5">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="isNewFolderOpen = true"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    ]"
                  >
                    <FolderPlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Folder
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="triggerFileUpload"
                    :disabled="isUploading"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed',
                    ]"
                  >
                    <ArrowUpTrayIcon class="h-5 w-5" aria-hidden="true" />
                    {{ isUploading ? 'Uploading…' : 'Upload Files' }}
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="triggerFolderUpload"
                    :disabled="isUploading"
                    :class="[
                      active ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'text-slate-700 dark:text-slate-300',
                      'group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed',
                    ]"
                  >
                    <ArrowUpTrayIcon class="h-5 w-5" aria-hidden="true" />
                    {{ isUploading ? 'Uploading…' : 'Upload Folder' }}
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>

        <input ref="fileInput" type="file" multiple class="hidden" @change="handleFilesSelected" />
        <input
          ref="folderInput"
          type="file"
          webkitdirectory
          multiple
          class="hidden"
          @change="handleFolderSelected"
        />

        <NewFolderDialog
          :open="isNewFolderOpen"
          @close="isNewFolderOpen = false"
          @created="handleFolderCreated"
        />

        <RouterLink
          to="/dashboard"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <Squares2X2Icon class="w-5 h-5 shrink-0" />
          <span>Dashboard</span>
        </RouterLink>

        <RouterLink
          to="/my-files"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <FolderIcon class="w-5 h-5 shrink-0" />
          <span>My Files</span>
        </RouterLink>

        <RouterLink
          to="/profile"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <UserIcon class="w-5 h-5 shrink-0" />
          <span>Profile</span>
        </RouterLink>

        <RouterLink
          to="/trash"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <TrashIcon class="w-5 h-5 shrink-0" />
          <span>Trash</span>
        </RouterLink>

        <RouterLink
          to="/shared"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <ShareIcon class="w-5 h-5 shrink-0" />
          <span>Shared</span>
        </RouterLink>

        <RouterLink
          to="/starred"
          active-class="bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 font-semibold"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <StarIcon class="w-5 h-5 shrink-0" />
          <span>Starred</span>
        </RouterLink>
      </nav>

      <!-- User Quick Info & Logout Footer -->
      <div
        class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between"
      >
        <div class="truncate">
          <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
            {{ authStore.user?.name || 'User' }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
            {{ authStore.user?.email }}
          </p>
        </div>
        <button
          @click="handleLogout"
          title="Logout"
          class="p-2 text-slate-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition"
        >
          <ArrowLeftStartOnRectangleIcon class="w-5 h-5" />
        </button>
      </div>
    </aside>

    <!-- Right Side Body Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Top Header Navigation -->
      <header
        class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 lg:px-8 flex items-center justify-between sticky top-0 z-30"
      >
        <!-- Mobile Sidebar Hamburger Toggle -->
        <button
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          class="lg:hidden p-2 -ml-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
          <Bars3Icon class="w-5.5 h-5.5" />
        </button>

        <!-- Search -->
        <div class="relative flex-1 max-w-md mx-2 lg:mx-6">
          <MagnifyingGlassIcon
            class="w-4.5 h-4.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
          />
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            @keyup.enter="onSearchInput"
            type="text"
            placeholder="Search files and folders…"
            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-transparent transition"
          />
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-2">
          <ThemeToggle />

          <div class="relative">
            <button
              @click="isUserMenuOpen = !isUserMenuOpen"
              class="rounded-full ring-2 ring-violet-100 dark:ring-violet-900/50 hover:ring-violet-300 dark:hover:ring-violet-700 transition"
            >
              <UserAvatar />
            </button>

            <div
              v-if="isUserMenuOpen"
              @click="isUserMenuOpen = false"
              class="fixed inset-0 z-40"
            ></div>

            <div
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-3 w-64 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 shadow-xl shadow-slate-900/10 dark:shadow-slate-950/40 backdrop-blur-sm z-50 overflow-hidden"
            >
              <div
                class="absolute -top-2 right-4 h-4 w-4 rotate-45 border-l border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
              ></div>

              <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                  <UserAvatar size="w-10 h-10" />
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                      {{ authStore.user?.name || 'User' }}
                    </p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                      {{ authStore.user?.email || 'your@email.com' }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="p-2">
                <RouterLink
                  :to="{ name: 'profile' }"
                  @click="isUserMenuOpen = false"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                >
                  <UserIcon class="h-4 w-4" />
                  Profile
                </RouterLink>

                <button
                  @click="handleLogout"
                  class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-950/30"
                >
                  <ArrowLeftStartOnRectangleIcon class="h-4 w-4" />
                  Log out
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Dynamic Content Slot -->
      <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
