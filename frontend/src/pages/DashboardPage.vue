<script setup>
import { onMounted, ref } from 'vue'
import { User, Calendar, ClipboardList } from 'lucide-vue-next'
import { getOptimized, batch } from '../services/apiOptimized'
import UnifiedCard from '../components/common/UnifiedCard.vue'

const attendances = ref([])
const loading = ref(true)
const user = ref(null)
const error = ref(null)

const fetchData = async () => {
  loading.value = true
  try {
    const [attendanceData, userData] = await batch([
      ['/attendances', { cache: true, cacheTTL: 60000 }],
      ['/user/profile', { cache: true, cacheTTL: 300000 }]
    ])
    
    attendances.value = Array.isArray(attendanceData) ? attendanceData : []
    user.value = userData
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
    error.value = 'Failed to load dashboard data'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="main-content">
    <!-- Welcome Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Dashboard</h1>
        <p class="text-slate-600 mt-1">Welcome to your attendance management system</p>
      </div>
      <div v-if="user" class="flex items-center gap-3 bg-slate-50 rounded-xl p-4 border border-slate-200">
        <div class="size-12 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
          {{ user.name?.charAt(0) || 'U' }}
        </div>
        <div>
          <p class="font-bold text-slate-900">{{ user.name || 'User' }}</p>
          <p class="text-sm text-slate-500">{{ user.email }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <UnifiedCard variant="primary" size="sm">
        <template #header>
          <div class="flex items-center gap-3">
            <div class="size-10 bg-primary/20 rounded-xl flex items-center justify-center">
              <Calendar class="size-5 text-primary" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900">Today's Attendance</h3>
              <p class="text-sm text-slate-500">Overview of today's records</p>
            </div>
          </div>
        </template>
        <div class="text-3xl font-bold text-slate-900">{{ attendances.length }}</div>
        <p class="text-sm text-slate-500 mt-1">Total records</p>
      </UnifiedCard>

      <UnifiedCard variant="success" size="sm">
        <template #header>
          <div class="flex items-center gap-3">
            <div class="size-10 bg-success/20 rounded-xl flex items-center justify-center">
              <ClipboardList class="size-5 text-success-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900">Recent Activity</h3>
              <p class="text-sm text-slate-500">Latest attendance updates</p>
            </div>
          </div>
        </template>
        <div class="text-3xl font-bold text-slate-900">{{ attendances.filter(a => a.status === 'present').length }}</div>
        <p class="text-sm text-slate-500 mt-1">Present today</p>
      </UnifiedCard>

      <UnifiedCard variant="warning" size="sm">
        <template #header>
          <div class="flex items-center gap-3">
            <div class="size-10 bg-warning/20 rounded-xl flex items-center justify-center">
              <User class="size-5 text-warning-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900">User Profile</h3>
              <p class="text-sm text-slate-500">Your account information</p>
            </div>
          </div>
        </template>
        <div class="text-3xl font-bold text-slate-900">{{ user ? 'Active' : 'Loading' }}</div>
        <p class="text-sm text-slate-500 mt-1">Account status</p>
      </UnifiedCard>
    </div>

    <!-- Recent Records -->
    <UnifiedCard title="Recent Attendance Records" subtitle="Latest attendance entries from the system">
      <div v-if="loading" class="flex items-center justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span class="ml-3 text-slate-600">Loading attendance...</span>
      </div>
      
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <div class="size-8 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="size-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <p class="font-medium text-red-900">Error</p>
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>
        </div>
        <button @click="fetchAttendances" class="mt-3 btn btn-secondary">
          Try Again
        </button>
      </div>
      
      <div v-else-if="attendances.length === 0" class="text-center py-8 text-slate-500">
        <div class="text-4xl mb-2">📊</div>
        <p class="text-lg font-semibold">No attendance records found</p>
        <p class="text-sm mt-1">Records will appear here once attendance is taken</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="item in attendances" 
          :key="item.id" 
          class="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <div>
            <p class="font-semibold text-slate-900">{{ item.date || 'Unknown date' }}</p>
            <p class="text-sm text-slate-500">{{ item.student?.name || 'Unknown student' }}</p>
          </div>
          <span 
            class="px-3 py-1 rounded-full text-sm font-medium"
            :class="{
              'bg-success-100 text-success-800': item.status === 'present',
              'bg-danger-100 text-danger-800': item.status === 'absent',
              'bg-warning-100 text-warning-800': item.status === 'late',
              'bg-slate-100 text-slate-800': !['present', 'absent', 'late'].includes(item.status || '')
            }"
          >
            {{ item.status || 'Unknown' }}
          </span>
        </div>
      </div>
    </UnifiedCard>
  </div>
</template>
