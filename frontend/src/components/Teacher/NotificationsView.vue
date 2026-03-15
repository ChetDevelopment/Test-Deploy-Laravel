<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Bell, CheckCircle2, Clock, MoreHorizontal, AlertTriangle, GraduationCap } from 'lucide-vue-next'
import { teacherService } from '../../services/teacherService'
import { notificationService } from '../../services/notificationService'

const notifications = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')

const loadNotifications = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    // Load teacher activities (existing)
    const teacherNotifs = await teacherService.getNotifications()
    
    // Also try to load absence notifications
    let absenceNotifs: any[] = []
    try {
      const response = await notificationService.getNotifications()
      absenceNotifs = Array.isArray(response?.notifications) ? response.notifications : []
    } catch (e) {
      // Silently continue if absence notifications fail
      console.debug('Could not load absence notifications')
    }
    
    // Combine and sort by date
    const allNotifs = [
      ...(Array.isArray(teacherNotifs) ? teacherNotifs : []).map(n => ({ ...n, source: 'activity' })),
      ...absenceNotifs.map(n => ({ 
        id: `absence-${n.id}`, 
        title: n.title, 
        message: n.message, 
        time: n.created_at ? new Date(n.created_at).toLocaleDateString() : 'Unknown',
        type: n.type === 'absence_alert' ? 'warning' : 'reminder',
        unread: !n.read,
        source: 'absence'
      }))
    ].sort((a, b) => (b.unread ? 1 : 0) - (a.unread ? 1 : 0))
    
    notifications.value = allNotifs
  } catch (error: any) {
    errorMessage.value = error.message || 'Failed to load notifications.'
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id: number | string) => {
  const n = notifications.value.find((notif) => notif.id === id)
  if (!n) return
  
  // Only mark as read on backend if it's an absence notification
  const idStr = String(id)
  if (idStr.startsWith('absence-')) {
    try {
      await notificationService.markAsRead(idStr.replace('absence-', ''))
    } catch (e) {
      console.debug('Could not mark notification as read')
    }
  }
  n.unread = false
}

const deleteNotification = async (id: number | string) => {
  const n = notifications.value.find((notif) => notif.id === id)
  if (!n) return
  
  // Only delete on backend if it's an absence notification
  const idStr = String(id)
  if (idStr.startsWith('absence-')) {
    try {
      await notificationService.deleteNotification(idStr.replace('absence-', ''))
    } catch (e) {
      console.debug('Could not delete notification')
    }
  }
  notifications.value = notifications.value.filter((notif) => notif.id !== id)
}

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'success': return CheckCircle2
    case 'warning': return AlertTriangle
    case 'reminder': return Clock
    default: return Bell
  }
}

onMounted(loadNotifications)
</script>

<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-3xl font-black tracking-tight text-slate-900">Notifications</h2>
        <p class="text-slate-500 font-medium">Teacher activity updates from backend</p>
      </div>
      <button class="text-xs font-bold text-primary hover:underline" @click="loadNotifications">Refresh</button>
    </div>

    <p v-if="errorMessage" class="p-3 rounded-lg bg-rose-50 text-rose-700 text-sm">{{ errorMessage }}</p>
    <div v-if="loading" class="text-sm text-slate-500">Loading notifications...</div>

    <div class="space-y-3">
      <div
        v-for="n in notifications"
        :key="n.id"
        :class="`bg-white p-5 rounded-2xl border transition-all group relative ${
          n.unread ? 'border-primary/20 bg-primary/[0.01] shadow-md shadow-primary/5' : 'border-slate-200 opacity-80'
        }`"
      >
        <div v-if="n.unread" class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-l-2xl" />

        <div class="flex gap-4">
          <div class="size-12 rounded-xl flex items-center justify-center shrink-0 bg-slate-100 text-slate-600">
            <component :is="getNotificationIcon(n.type)" v-if="n.type" :size="20" />
            <Bell v-else :size="20" />
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start mb-1">
              <h4 class="font-bold text-slate-900">{{ n.title }}</h4>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ n.time }}</span>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed mb-3">{{ n.message }}</p>

            <div class="flex items-center gap-3">
              <button
                v-if="n.unread"
                @click="markAsRead(n.id)"
                class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline"
              >
                Mark as read
              </button>
              <button
                @click="deleteNotification(n.id)"
                class="text-[10px] font-black text-red-500 uppercase tracking-widest hover:underline opacity-0 group-hover:opacity-100 transition-opacity"
              >
                Delete
              </button>
            </div>
          </div>

          <button class="p-1 text-slate-300 hover:text-slate-600">
            <MoreHorizontal :size="18" />
          </button>
        </div>
      </div>

      <div v-if="!loading && notifications.length === 0" class="py-20 text-center space-y-4">
        <div class="size-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-200">
          <Bell :size="40" />
        </div>
        <p class="text-slate-400 font-medium">You're all caught up!</p>
      </div>
    </div>
  </div>
</template>
