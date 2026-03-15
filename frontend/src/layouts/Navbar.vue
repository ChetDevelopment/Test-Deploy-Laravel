<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Search, Bell, LogOut, User, Settings, CheckCircle2, XCircle } from 'lucide-vue-next';
import api from '../services/api';
import { notificationService } from '../services/notificationService';
import { clearStudentSession, clearToken, clearUser, clearUserRole, getToken, getUser, setUser, authUser } from '../services/auth';
import { profileService } from '../services/profileService';

const emit = defineEmits<{
  navigate: [module: string];
}>();

const isProfileOpen = ref(false);
const isNotificationsOpen = ref(false);
const isLoggingOut = ref(false);
const router = useRouter();

// Notification state
interface Notification {
  id: number;
  title: string;
  subtitle: string;
  type: string;
  read: boolean;
  created_at: string;
}

const notifications = ref<Notification[]>([]);
const notificationLoading = ref(false);
const notificationError = ref('');

// Get current user from reactive state
const currentUser = computed(() => authUser.value);
const userName = computed(() => currentUser.value?.name || 'User');
const userRole = computed(() => {
  const role = currentUser.value?.role;
  if (role === 'admin') return 'Administrator';
  if (role === 'teacher') return 'Teacher';
  if (role === 'education') return 'Education Staff';
  if (role === 'student') return 'Student';
  return 'User';
});
const userAvatar = computed(() => currentUser.value?.avatar_url || null);
const userInitials = computed(() => {
  const name = String(userName.value || '').trim();
  if (!name) return 'U';

  const parts = name.split(/\s+/).filter(Boolean);
  if (parts.length === 1) return parts[0].slice(0, 1).toUpperCase();

  return `${parts[0].slice(0, 1)}${parts[parts.length - 1].slice(0, 1)}`.toUpperCase();
});
const resolvedAvatarUrl = computed(() => {
  const avatar = userAvatar.value;
  if (!avatar) return null;
  if (/^(https?:|data:|blob:)/i.test(avatar)) return avatar;

  const apiBase = import.meta.env.VITE_API_BASE_URL || '/api';
  const apiOrigin = /^https?:\/\//i.test(apiBase)
    ? new URL(apiBase).origin
    : window.location.origin;

  return avatar.startsWith('/') ? `${apiOrigin}${avatar}` : `${apiOrigin}/${avatar}`;
});

// Computed properties for notifications
const unreadNotifications = computed(() => notifications.value.filter(n => !n.read));
const hasUnread = computed(() => unreadNotifications.value.length > 0);

const navigateTo = (module: string) => {
  emit('navigate', module);
  isProfileOpen.value = false;
};

const loadNotifications = async (retryCount = 0) => {
  if (!getToken()) return;
  
  if (retryCount === 0) {
    notificationLoading.value = true;
  }
  notificationError.value = '';
  
  try {
    const data = await notificationService.getNotifications();
    // Handle both array and object format for backward compatibility
    if (Array.isArray(data)) {
      notifications.value = data;
    } else if (data && Array.isArray(data.notifications)) {
      notifications.value = data.notifications;
    } else {
      notifications.value = [];
    }
  } catch (error) {
    console.error('Failed to load notifications:', error);
    
    // Retry once after a delay if it's a timeout or network error
    if (retryCount < 1) {
      setTimeout(() => loadNotifications(retryCount + 1), 2000);
      return;
    }
    
    notificationError.value = 'Failed to load notifications';
    notifications.value = [];
  } finally {
    if (retryCount >= 0) {
      notificationLoading.value = false;
    }
  }
};

const markAllAsRead = async () => {
  if (!getToken() || notifications.value.length === 0) return;
  
  try {
    await notificationService.markAllAsRead();
    notifications.value = notifications.value.map(n => ({ ...n, read: true }));
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
  }
};

const handleLogout = async () => {
  if (isLoggingOut.value) return;

  isLoggingOut.value = true;

  try {
    if (getToken()) {
      // Add a short timeout to the logout call to avoid hanging when the server is slow/down
      await api.post('/auth/logout', null, { timeout: 3000 });
    }
  } catch {
    // Always clear local token and continue to login page even if API fails.
  } finally {
    clearToken();
    clearStudentSession();
    clearUser();
    clearUserRole();
    isProfileOpen.value = false;
    isNotificationsOpen.value = false;
    isLoggingOut.value = false;
    router.push({ name: 'login' });
  }
};

onMounted(() => {
  if (getToken()) {
    profileService.getProfile()
      .then((profile) => {
        const existing = getUser() || {};
        setUser({ ...existing, ...profile });
      })
      .catch(() => {
        // Keep navbar usable even if profile fetch fails.
      });
  }

  loadNotifications();
});
</script>

<template>
  <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-20">
    <div class="flex items-center flex-1 max-w-md">
      <div class="relative w-full">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 size-5" />
        <input
          type="text"
          placeholder="Search student records..."
          class="w-full bg-slate-100 border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary/50 transition-all outline-none"
        />
      </div>
    </div>

    <div class="flex items-center gap-4">
      <div class="relative">
        <button
          @click="isNotificationsOpen = !isNotificationsOpen"
          class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors"
        >
          <Bell class="size-5" />
          <span v-if="hasUnread" class="absolute top-2 right-2 size-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

        <div v-if="isNotificationsOpen" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-30">
          <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Notifications</span>
            <span class="text-[10px] text-primary font-bold cursor-pointer hover:underline">Mark all read</span>
          </div>
          <div class="max-h-64 overflow-y-auto">
            <div v-if="notificationLoading" class="px-4 py-3 text-center text-[10px] text-slate-400">
              Loading notifications...
            </div>
            <div v-else-if="notificationError" class="px-4 py-3 text-center text-[10px] text-red-500">
              {{ notificationError }}
            </div>
            <div v-else-if="notifications.length === 0" class="px-4 py-3 text-center text-[10px] text-slate-400 italic">
              No notifications at this time
            </div>
            <div
              v-for="n in notifications"
              :key="n.id"
              class="px-4 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-0 cursor-pointer"
            >
              <div class="flex items-start gap-3">
                <div :class="[
                  'size-2 rounded-full mt-1 flex-shrink-0',
                  n.read ? 'bg-slate-300' : 'bg-primary'
                ]"></div>
                <div class="flex-1">
                  <p class="text-xs text-slate-800 font-medium">{{ n.title }}</p>
                  <p class="text-[10px] text-slate-400 mt-1">{{ n.subtitle }}</p>
                  <p class="text-[10px] text-slate-300 mt-1">{{ new Date(n.created_at).toLocaleString() }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-2 border-t border-slate-100">
            <button 
              @click="markAllAsRead"
              class="w-full text-[10px] text-primary font-bold hover:text-primary/80 transition-colors"
            >
              Mark all read
            </button>
          </div>
        </div>
      </div>

      <div class="h-6 w-px bg-slate-200"></div>

      <div class="relative">
        <button
          @click="isProfileOpen = !isProfileOpen"
          class="flex items-center gap-3 hover:bg-slate-50 p-1 rounded-lg transition-colors"
        >
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold leading-tight text-slate-900">{{ userName }}</p>
            <p class="text-[10px] text-slate-500 font-medium">{{ userRole }}</p>
          </div>
          <div class="size-10 rounded-full bg-slate-200 overflow-hidden border border-slate-200">
            <img
              v-if="resolvedAvatarUrl"
              :src="resolvedAvatarUrl"
              alt="User profile"
              class="w-full h-full object-cover"
              referrerpolicy="no-referrer"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-xs font-black text-slate-600">
              {{ userInitials }}
            </div>
          </div>
        </button>

        <div v-if="isProfileOpen" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-30">
          <button
            @click="navigateTo('profile')"
            class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2"
          >
            <User class="size-4" />
            My Profile
          </button>
          <button
            @click="navigateTo('settings')"
            class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2"
          >
            <Settings class="size-4" />
            Settings
          </button>
          <div class="h-px bg-slate-100 my-1"></div>
          <button
            @click="handleLogout"
            :disabled="isLoggingOut"
            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <LogOut class="size-4" />
            Logout
          </button>
        </div>
      </div>

      <button
        @click="handleLogout"
        :disabled="isLoggingOut"
        class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors sm:hidden disabled:opacity-60 disabled:cursor-not-allowed"
      >
        <LogOut class="size-5" />
      </button>
    </div>
  </header>
</template>
