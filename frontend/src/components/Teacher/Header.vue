<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Search, Bell, Settings, LogOut } from 'lucide-vue-next'
import { ViewType } from './Sidebar.vue'
import { notificationService } from '../../services/notificationService'

interface User {
  name: string;
  role: 'teacher' | 'admin';
  department?: string;
  photo?: string;
}

defineProps<{
  user: User;
}>();

const emit = defineEmits<{
  (e: 'navigate', view: ViewType): void;
  (e: 'logout'): void;
}>();

const unreadCount = ref(0)

const loadUnreadCount = async () => {
  try {
    const response = await notificationService.getUnreadCount()
    if (response?.data?.count !== undefined) {
      unreadCount.value = response.data.count
    }
  } catch (error) {
    // Silently fail - keep default count
    console.debug('Could not load notification count')
  }
}

onMounted(() => {
  loadUnreadCount()
  // Refresh count every 30 seconds
  setInterval(loadUnreadCount, 30000)
})
</script>

<template>
  <header class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-8 sticky top-0 z-10">
    <div class="flex items-center gap-4 flex-1">
      <div class="relative w-full max-w-md">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
        <input 
          type="text" 
          placeholder="Search students, classes, or reports..."
          class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        />
      </div>
    </div>
    
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-full border border-slate-100">
        <div class="size-2 bg-emerald-500 rounded-full animate-pulse" />
        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ user.role }} mode</span>
      </div>
      <button 
        @click="emit('navigate', 'notifications')"
        class="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors"
      >
        <Bell :size="20" />
        <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>
      <button 
        @click="emit('navigate', 'settings')"
        class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors"
      >
        <Settings :size="20" />
      </button>
    </div>
  </header>
</template>
