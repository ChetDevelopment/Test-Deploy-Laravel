<script setup lang="ts">
import { computed, ref } from 'vue';
import { 
  LayoutDashboard,
  ShieldCheck,
  Network,
  Users,
  Calendar,
  Settings,
  Headphones,
  ClipboardList,
  User,
  LogOut,
  ChevronDown
} from 'lucide-vue-next';

export type NavItem = {
  id: string;
  icon: any;
  label: string;
  children?: NavItem[];
};

export interface User {
  name: string;
  role: 'student' | 'teacher' | 'admin' | 'education';
  department?: string;
  photo?: string;
}

const props = defineProps<{
  currentModule: string;
  user: User;
  mockUsers?: User[];
  theme?: 'light' | 'dark';
}>();

const emit = defineEmits<{
  moduleChange: [module: string];
  userChange: [user: User];
  logout: [];
}>();

const isUserDropdownOpen = ref(false);

// Navigation items based on user role
const navItems = computed((): NavItem[] => {
  switch (props.user.role) {
    case 'student':
      return [
        { id: 'dashboard', icon: LayoutDashboard, label: 'Dashboard' },
        { id: 'attendance', icon: Calendar, label: 'Attendance' },
        { id: 'absences', icon: ClipboardList, label: 'Absences' },
      ];
    case 'teacher':
      return [
        { id: 'dashboard', icon: LayoutDashboard, label: 'Dashboard' },
        { id: 'schedule', icon: Calendar, label: 'Schedule' },
        { id: 'attendance', icon: Calendar, label: 'Attendance Control' },
        { id: 'history', icon: ClipboardList, label: 'History' },
        { id: 'management', icon: Users, label: 'Student Management' },
        { id: 'messages', icon: Headphones, label: 'Messages' },
      ];
    case 'education':
      return [
        { id: 'dashboard', icon: LayoutDashboard, label: 'Dashboard' },
        { id: 'users', icon: ShieldCheck, label: 'User & Permission' },
        { id: 'academic', icon: Network, label: 'Academic Structure' },
        { id: 'students', icon: Users, label: 'Student Management' },
        { id: 'attendance', icon: Calendar, label: 'Attendance Control' },
        { id: 'absences', icon: ClipboardList, label: 'Absence Management' },
        { id: 'reports', icon: ClipboardList, label: 'Reports' },
        { id: 'risk', icon: ClipboardList, label: 'Risk Monitoring' },
      ];
    case 'admin':
    default:
      return [
        { id: 'dashboard', icon: LayoutDashboard, label: 'Dashboard' },
        { id: 'users', icon: ShieldCheck, label: 'User & Permission' },
        { id: 'academic', icon: Network, label: 'Academic Structure' },
        { id: 'students', icon: Users, label: 'Student Management' },
        { id: 'attendance', icon: Calendar, label: 'Attendance Control' },
        { id: 'absences', icon: ClipboardList, label: 'Absence Management' },
        { id: 'settings', icon: Settings, label: 'System Settings' },
      ];
  }
});

const handleUserChange = (user: User) => {
  emit('userChange', user);
  isUserDropdownOpen.value = false;
};

const handleLogout = () => {
  emit('logout');
  isUserDropdownOpen.value = false;
};
</script>

<template>
  <aside class="sidebar" :class="{ 'bg-slate-800': theme === 'dark' }">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="flex items-center gap-3">
        <div class="size-10 rounded-lg border border-slate-200 bg-white flex items-center justify-center p-1.5 dark:border-slate-700 dark:bg-slate-700">
          <img src="/PictureUseInPageLogin.png" alt="Website logo" class="size-7 object-contain" />
        </div>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">វត្តមាន-Attendance</h1>
          <p class="text-[10px] text-slate-500 font-bold tracking-wider dark:text-slate-400">វត្តមាន-Attendance</p>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <button
        v-for="item in navItems"
        :key="item.id"
        @click="emit('moduleChange', item.id)"
        :class="[
          'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all group',
          currentModule === item.id
            ? 'bg-primary text-white shadow-sm'
            : 'text-slate-600 hover:bg-primary/10 hover:text-primary dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-700',
        ]"
      >
        <component
          :is="item.icon"
          :class="[
            'size-5',
            currentModule === item.id ? 'text-white' : 'text-slate-500 group-hover:text-primary dark:text-slate-400',
          ]"
        />
        <span class="text-sm font-semibold">{{ item.label }}</span>
      </button>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <!-- User Info -->
      <div class="relative">
        <button 
          @click="isUserDropdownOpen = !isUserDropdownOpen"
          class="w-full flex items-center gap-3 p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors dark:bg-slate-700 dark:hover:bg-slate-600"
        >
          <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center dark:bg-primary/30">
            <User class="size-4 text-primary" />
          </div>
          <div class="flex-1 text-left">
            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ user.department || user.role }}</p>
          </div>
          <ChevronDown 
            :class="[
              'size-4 text-slate-400 transition-transform',
              isUserDropdownOpen ? 'rotate-180' : ''
            ]" 
          />
        </button>

        <!-- User Dropdown -->
        <div 
          v-if="isUserDropdownOpen && mockUsers && mockUsers.length > 1"
          class="absolute bottom-full left-0 w-full mb-2 bg-white border border-slate-200 rounded-xl shadow-xl dark:bg-slate-700 dark:border-slate-600 p-2"
        >
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 py-2 dark:text-slate-300">Switch Account</p>
          <button
            v-for="u in mockUsers"
            :key="u.name"
            @click="handleUserChange(u)"
            :class="[
              'w-full flex items-center gap-3 p-2 rounded-lg text-left transition-colors',
              user.name === u.name 
                ? 'bg-primary/5 text-primary dark:bg-slate-600' 
                : 'hover:bg-slate-50 dark:hover:bg-slate-600'
            ]"
          >
            <div class="size-6 rounded-full bg-slate-200 dark:bg-slate-500 flex items-center justify-center">
              <User class="size-3 text-slate-600 dark:text-slate-200" />
            </div>
            <span class="text-xs font-bold">{{ u.name }}</span>
          </button>
          <div class="border-t border-slate-200 my-2 dark:border-slate-600"></div>
          <button
            @click="handleLogout"
            class="w-full flex items-center gap-3 p-2 rounded-lg text-left text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-slate-600 transition-colors"
          >
            <LogOut :size="16" />
            <span class="text-xs font-bold">Logout</span>
          </button>
        </div>
      </div>

      <!-- Help Section -->
      <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl dark:bg-slate-700">
        <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center dark:bg-primary/30">
          <Headphones class="size-4 text-primary" />
        </div>
        <div>
          <p class="text-xs font-bold text-slate-900 dark:text-white">Help Desk</p>
          <p class="text-[10px] text-slate-500 dark:text-slate-400">Contact Support</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
/* Additional styles for the unified sidebar */
.sidebar {
  transition: transform var(--transition-normal);
}

.sidebar-header {
  border-bottom: 1px solid var(--slate-200);
}

.sidebar-nav {
  border-bottom: 1px solid var(--slate-200);
}

.sidebar-footer {
  border-top: 1px solid var(--slate-200);
}

/* Mobile responsive styles */
@media (max-width: 1024px) {
  .sidebar {
    position: fixed;
    z-index: var(--z-sticky);
    transform: translateX(-100%);
  }
  
  .sidebar.open {
    transform: translateX(0);
  }
}
</style>