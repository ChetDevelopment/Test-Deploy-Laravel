<script setup lang="ts">
import { computed, ref } from 'vue';
import { 
  Bell,
  Settings,
  User,
  Menu,
  X,
  Search,
  Sun,
  Moon,
  ChevronDown
} from 'lucide-vue-next';

export interface User {
  name: string;
  role: string;
  photo?: string;
}

const props = defineProps<{
  user: User;
  theme?: 'light' | 'dark';
  notifications?: number;
}>();

const emit = defineEmits<{
  toggleSidebar: [];
  navigate: [path: string];
  logout: [];
  toggleTheme: [];
}>();

const isUserDropdownOpen = ref(false);
const isNotificationsOpen = ref(false);
const searchQuery = ref('');

const themeIcon = computed(() => props.theme === 'dark' ? Sun : Moon);
const themeLabel = computed(() => props.theme === 'dark' ? 'Light Mode' : 'Dark Mode');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    emit('navigate', `/search?q=${encodeURIComponent(searchQuery.value)}`);
  }
};

const toggleTheme = () => {
  emit('toggleTheme');
};

const toggleUserDropdown = () => {
  isUserDropdownOpen.value = !isUserDropdownOpen.value;
  if (isUserDropdownOpen.value) {
    isNotificationsOpen.value = false;
  }
};

const toggleNotifications = () => {
  isNotificationsOpen.value = !isNotificationsOpen.value;
  if (isNotificationsOpen.value) {
    isUserDropdownOpen.value = false;
  }
};
</script>

<template>
  <header class="header" :class="{ 'bg-slate-800': theme === 'dark' }">
    <!-- Left Section -->
    <div class="flex items-center gap-4">
      <!-- Mobile Menu Button -->
      <button
        @click="emit('toggleSidebar')"
        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors md:hidden"
      >
        <Menu v-if="!isUserDropdownOpen && !isNotificationsOpen" :size="20" />
        <X v-else :size="20" />
      </button>

      <!-- Search Bar -->
      <div class="relative hidden md:block">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 size-4" />
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="Search..."
          class="pl-10 pr-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
        />
      </div>
    </div>

    <!-- Right Section -->
    <div class="flex items-center gap-2">
      <!-- Theme Toggle -->
      <button
        @click="toggleTheme"
        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        title="Toggle theme"
      >
        <component :is="themeIcon" :size="20" />
      </button>

      <!-- Notifications -->
      <div class="relative">
        <button
          @click="toggleNotifications"
          class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors relative"
        >
          <Bell :size="20" />
          <span
            v-if="notifications && notifications > 0"
            class="absolute -top-1 -right-1 bg-rose-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ Math.min(notifications, 99) }}
          </span>
        </button>

        <!-- Notifications Dropdown -->
        <div
          v-if="isNotificationsOpen"
          class="absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl dark:bg-slate-800 dark:border-slate-700 z-50"
        >
          <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">Notifications</h3>
          </div>
          <div class="p-4">
            <div v-if="notifications && notifications > 0" class="space-y-3">
              <div
                v-for="n in Math.min(notifications, 5)"
                :key="n"
                class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg dark:bg-slate-700"
              >
                <div class="size-8 bg-primary/20 rounded-full flex items-center justify-center">
                  <Bell class="size-4 text-primary" />
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">New notification {{ n }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Just now</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4 text-slate-500 dark:text-slate-400">
              No new notifications
            </div>
          </div>
          <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            <button
              @click="toggleNotifications"
              class="w-full text-center text-sm text-primary hover:text-primary/80"
            >
              View All
            </button>
          </div>
        </div>
      </div>

      <!-- Settings -->
      <button
        @click="emit('navigate', '/settings')"
        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        title="Settings"
      >
        <Settings :size="20" />
      </button>

      <!-- User Menu -->
      <div class="relative">
        <button
          @click="toggleUserDropdown"
          class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        >
          <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center">
            <User class="size-4 text-primary" />
          </div>
          <div class="hidden md:block text-left">
            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ user.role }}</p>
          </div>
          <ChevronDown :size="16" class="hidden md:block text-slate-400" />
        </button>

        <!-- User Dropdown -->
        <div
          v-if="isUserDropdownOpen"
          class="absolute right-0 top-full mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl dark:bg-slate-800 dark:border-slate-700 z-50"
        >
          <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ user.name }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.role }}</p>
          </div>
          <div class="p-2">
            <button
              @click="emit('navigate', '/profile')"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            >
              <User :size="16" />
              Profile
            </button>
            <button
              @click="toggleTheme"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            >
              <component :is="themeIcon" :size="16" />
              {{ themeLabel }}
            </button>
            <button
              @click="emit('navigate', '/settings')"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            >
              <Settings :size="16" />
              Settings
            </button>
          </div>
          <div class="border-t border-slate-200 dark:border-slate-700 p-2">
            <button
              @click="emit('logout')"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left text-sm text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-slate-700 transition-colors"
            >
              <User :size="16" />
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Additional styles for the unified header */
.header {
  transition: background-color var(--transition-normal);
}

/* Mobile search styles */
@media (max-width: 768px) {
  .header {
    padding: 0 1rem;
  }
  
  .search-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: white;
    border-bottom: 1px solid var(--slate-200);
    z-index: 1000;
  }
}
</style>