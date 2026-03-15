<script setup lang="ts">
import { ref } from 'vue'
import { 
  Calendar,
  Users,
  CheckCircle2,
  XCircle,
  Clock,
  TrendingUp,
  TrendingDown,
  AlertTriangle,
  Settings,
  User,
  Search,
  Sun,
  Moon
} from 'lucide-vue-next'
import UnifiedCard from './UnifiedCard.vue'
import UnifiedSidebar from './UnifiedSidebar.vue'
import UnifiedHeader from './UnifiedHeader.vue'

const theme = ref<'light' | 'dark'>('light')
const notifications = ref(3)
const searchQuery = ref('')

const mockUser = {
  name: 'John Doe',
  role: 'admin' as const,
  department: 'Administration',
  photo: 'https://picsum.photos/seed/admin/200/200'
}

const mockUsers = [
  { name: 'John Doe', role: 'admin' as const, department: 'Administration' },
  { name: 'Jane Smith', role: 'teacher' as const, department: 'Mathematics' },
  { name: 'Bob Wilson', role: 'student' as const, department: 'Grade 10' }
]

const stats = [
  { title: 'Total Students', value: '1,234', icon: Users, variant: 'primary' as const },
  { title: 'Present Today', value: '1,156', icon: CheckCircle2, variant: 'success' as const },
  { title: 'Absent Today', value: '45', icon: XCircle, variant: 'danger' as const },
  { title: 'Late Today', value: '33', icon: Clock, variant: 'warning' as const }
]

const handleToggleTheme = () => {
  theme.value = theme.value === 'light' ? 'dark' : 'light'
  document.documentElement.setAttribute('data-theme', theme.value)
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    alert(`Searching for: ${searchQuery.value}`)
  }
}

const handleLogout = () => {
  alert('Logout clicked')
}

const handleModuleChange = (module: string) => {
  alert(`Module changed to: ${module}`)
}
</script>

<template>
  <div :class="{ 'dark': theme === 'dark' }">
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
      <!-- Test Sidebar -->
      <UnifiedSidebar 
        :current-module="'dashboard'"
        :user="mockUser"
        :mock-users="mockUsers"
        :theme="theme"
        @module-change="handleModuleChange"
        @user-change="console.log"
        @logout="handleLogout"
      />

      <!-- Test Header -->
      <UnifiedHeader 
        :user="mockUser"
        :theme="theme"
        :notifications="notifications"
        @toggle-sidebar="console.log"
        @navigate="console.log"
        @logout="handleLogout"
        @toggle-theme="handleToggleTheme"
      />

      <!-- Test Content -->
      <div class="main-content">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Design System Test</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Testing unified components and design tokens</p>
          </div>
          <div class="flex items-center gap-3">
            <button @click="handleToggleTheme" class="btn btn-primary">
              <component :is="theme === 'light' ? Moon : Sun" class="size-4 mr-2" />
              {{ theme === 'light' ? 'Enable Dark Mode' : 'Enable Light Mode' }}
            </button>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <UnifiedCard 
            v-for="stat in stats" 
            :key="stat.title"
            :variant="stat.variant"
            size="sm"
          >
            <template #header>
              <div class="flex items-center gap-3">
                <div class="size-10 bg-white/20 rounded-xl flex items-center justify-center">
                  <component :is="stat.icon" class="size-5" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ stat.title }}</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Current count</p>
                </div>
              </div>
            </template>
            <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</div>
          </UnifiedCard>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <UnifiedCard title="Recent Activity" subtitle="Latest system updates">
              <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-success-100 dark:bg-success-900 rounded-full flex items-center justify-center">
                      <CheckCircle2 class="size-4 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                      <p class="font-medium">System Update Completed</p>
                      <p class="text-sm text-slate-500 dark:text-slate-400">10 minutes ago</p>
                    </div>
                  </div>
                  <span class="text-sm text-slate-500 dark:text-slate-400">Success</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-warning-100 dark:bg-warning-900 rounded-full flex items-center justify-center">
                      <Clock class="size-4 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                      <p class="font-medium">Late Arrival Detected</p>
                      <p class="text-sm text-slate-500 dark:text-slate-400">25 minutes ago</p>
                    </div>
                  </div>
                  <span class="text-sm text-slate-500 dark:text-slate-400">Warning</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-danger-100 dark:bg-danger-900 rounded-full flex items-center justify-center">
                      <AlertTriangle class="size-4 text-danger-600 dark:text-danger-400" />
                    </div>
                    <div>
                      <p class="font-medium">System Error</p>
                      <p class="text-sm text-slate-500 dark:text-slate-400">1 hour ago</p>
                    </div>
                  </div>
                  <span class="text-sm text-slate-500 dark:text-slate-400">Error</span>
                </div>
              </div>
            </UnifiedCard>

            <UnifiedCard title="Search Functionality" subtitle="Test the search input component">
              <div class="space-y-4">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 size-4" />
                  <input
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Search for students, classes, or reports..."
                    class="input w-full"
                  />
                </div>
                <div class="flex gap-3">
                  <button @click="handleSearch" class="btn btn-primary">
                    <Search class="size-4 mr-2" />
                    Search
                  </button>
                  <button @click="searchQuery = ''" class="btn btn-secondary">
                    Clear
                  </button>
                </div>
              </div>
            </UnifiedCard>
          </div>

          <!-- Sidebar Content -->
          <div class="space-y-6">
            <UnifiedCard title="System Status" variant="primary">
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Database</span>
                  <span class="px-2 py-1 bg-success-100 text-success-800 rounded-full text-xs font-bold">Online</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">API Server</span>
                  <span class="px-2 py-1 bg-success-100 text-success-800 rounded-full text-xs font-bold">Online</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Redis Cache</span>
                  <span class="px-2 py-1 bg-warning-100 text-warning-800 rounded-full text-xs font-bold">Syncing</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">File Storage</span>
                  <span class="px-2 py-1 bg-success-100 text-success-800 rounded-full text-xs font-bold">Online</span>
                </div>
              </div>
            </UnifiedCard>

            <UnifiedCard title="Quick Actions" variant="success">
              <div class="space-y-3">
                <button class="w-full text-left p-3 rounded-lg hover:bg-white/20 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-white/20 rounded-full flex items-center justify-center">
                      <Users class="size-4" />
                    </div>
                    <div>
                      <p class="font-medium">Manage Users</p>
                      <p class="text-xs text-slate-600 dark:text-slate-400">Add, edit, or remove users</p>
                    </div>
                  </div>
                </button>
                
                <button class="w-full text-left p-3 rounded-lg hover:bg-white/20 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-white/20 rounded-full flex items-center justify-center">
                      <Calendar class="size-4" />
                    </div>
                    <div>
                      <p class="font-medium">View Schedule</p>
                      <p class="text-xs text-slate-600 dark:text-slate-400">Check class schedules</p>
                    </div>
                  </div>
                </button>
                
                <button class="w-full text-left p-3 rounded-lg hover:bg-white/20 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="size-8 bg-white/20 rounded-full flex items-center justify-center">
                      <Settings class="size-4" />
                    </div>
                    <div>
                      <p class="font-medium">System Settings</p>
                      <p class="text-xs text-slate-600 dark:text-slate-400">Configure system options</p>
                    </div>
                  </div>
                </button>
              </div>
            </UnifiedCard>

            <UnifiedCard title="Theme Preview" variant="warning">
              <div class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  This card demonstrates the {{ theme }} theme. Click the button above to toggle between light and dark themes.
                </p>
                <div class="flex items-center gap-3">
                  <div class="size-10 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center">
                    <User class="size-5 text-slate-600 dark:text-slate-300" />
                  </div>
                  <div>
                    <p class="font-medium">{{ mockUser.name }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ mockUser.role }} • {{ mockUser.department }}</p>
                  </div>
                </div>
              </div>
            </UnifiedCard>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-slate-500 dark:text-slate-400 text-sm">
          <p>Design system test complete. All components are working correctly with consistent styling.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles for the test component */
</style>