<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  TrendingUp, 
  Clock, 
  Calendar, 
  CheckCircle2, 
  X, 
  Info, 
  ChevronRight,
  TrendingDown,
  ArrowRight
} from 'lucide-vue-next';
import { fetchAttendanceHistory, fetchStudentDashboardStats } from '../../services/api';
import { AttendanceRecord } from '../types';

const attendanceHistory = ref<AttendanceRecord[]>([]);
const stats = ref<any>(null);

const periodLabel = computed(() => {
  const now = new Date();
  return now.toLocaleString('en-US', { month: 'long', year: 'numeric' });
});

const overallRate = computed(() => {
  const val = Number(stats.value?.monthlyPercentage ?? 0);
  return Number.isFinite(val) ? val : 0;
});

const presentDays = computed(() => Number(stats.value?.presentCount ?? 0));
const lateArrivals = computed(() => Number(stats.value?.lateCount ?? 0));
const absences = computed(() => Number(stats.value?.absencesCount ?? 0));

const recentRecords = computed(() => {
  return attendanceHistory.value.slice(0, 3);
});

const statusBreakdown = computed(() => {
  const records = attendanceHistory.value.slice(0, 50);
  const total = records.length || 1;
  const count = (status: AttendanceRecord['status']) => records.filter((r) => r.status === status).length;

  const present = count('PRESENT');
  const late = count('LATE');
  const absent = count('ABSENT');

  const percent = (value: number) => Math.round((value / total) * 100);

  return [
    { label: 'Present', val: percent(present), color: 'bg-green-500' },
    { label: 'Late', val: percent(late), color: 'bg-amber-400' },
    { label: 'Absent', val: percent(absent), color: 'bg-red-400' },
  ];
});

const trends = computed(() => {
  const weeks = 4;
  const now = new Date();
  const start = new Date(now);
  start.setDate(start.getDate() - (weeks * 7 - 1));
  start.setHours(0, 0, 0, 0);

  const buckets = Array.from({ length: weeks }, () => ({ total: 0, presentLike: 0 }));

  for (const record of attendanceHistory.value) {
    const ts = record.timestamp || record.date;
    if (!ts) continue;

    const date = new Date(ts);
    if (Number.isNaN(date.getTime())) continue;
    if (date < start) continue;

    const diffDays = Math.floor((date.getTime() - start.getTime()) / (24 * 60 * 60 * 1000));
    const idx = Math.min(weeks - 1, Math.max(0, Math.floor(diffDays / 7)));
    buckets[idx].total += 1;
    if (record.status === 'PRESENT' || record.status === 'LATE') buckets[idx].presentLike += 1;
  }

  return buckets.map((b) => (b.total ? Math.round((b.presentLike / b.total) * 100) : 0));
});

onMounted(async () => {
  try {
    stats.value = await fetchStudentDashboardStats();
    attendanceHistory.value = await fetchAttendanceHistory();
  } catch (err) {
    console.error(err);
  }
});
</script>

<template>
  <div class="p-8 space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight dark:text-white">Student Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Welcome back! Here's your attendance overview for this semester.</p>
      </div>
	      <div class="flex items-center gap-3">
	        <div class="bg-white dark:bg-slate-900 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-2">
	          <Calendar class="text-primary" :size="18" />
	          <span class="text-sm font-medium dark:text-white">{{ periodLabel }}</span>
	        </div>
	      </div>
	    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-primary">
            <TrendingUp :size="24" />
          </div>
	          <div>
	            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Overall Rate</p>
	            <h3 class="text-2xl font-bold dark:text-white">{{ overallRate }}%</h3>
	          </div>
	        </div>
	      </div>

      <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 rounded-full flex items-center justify-center text-green-500">
            <CheckCircle2 :size="24" />
          </div>
	          <div>
	            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Present (This Month)</p>
	            <h3 class="text-2xl font-bold dark:text-white">{{ presentDays }}</h3>
	          </div>
	        </div>
	      </div>

      <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center text-amber-500">
            <Clock :size="24" />
          </div>
	          <div>
	            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Late (This Month)</p>
	            <h3 class="text-2xl font-bold dark:text-white">{{ lateArrivals }}</h3>
	          </div>
	        </div>
	      </div>

      <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-500">
            <X :size="24" />
          </div>
	          <div>
	            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Absences</p>
	            <h3 class="text-2xl font-bold dark:text-white">{{ absences }}</h3>
	          </div>
	        </div>
	      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-8">
        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-lg font-bold dark:text-white">Attendance Trends</h3>
	          <div class="flex items-center gap-4">
	            <div class="flex items-center gap-1.5 text-xs text-slate-500">
	              <span class="w-2 h-2 rounded-full bg-primary"></span> Actual
	            </div>
	            <div class="flex items-center gap-1.5 text-xs text-slate-500">
	              <span class="w-2 h-2 rounded-full bg-slate-300"></span> Target ({{ stats?.targetPercentage ?? 75 }}%)
	            </div>
	          </div>
	        </div>
          <div class="w-full h-56 relative flex items-end justify-between px-2">
            <div class="absolute inset-0 flex flex-col justify-between py-1 pointer-events-none">
              <div v-for="val in [100, 75, 50, 25]" :key="val" class="border-t border-slate-100 dark:border-slate-700/50 w-full flex items-center justify-between">
                <span class="text-[10px] text-slate-400 pr-2">{{ val }}%</span>
              </div>
            </div>
	            <div v-for="(val, i) in trends" :key="i" class="w-[22%] bg-primary/20 rounded-t relative group cursor-pointer" :style="{ height: `${val}%` }">
	              <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">{{ val }}%</div>
	              <div class="absolute bottom-0 w-full bg-primary rounded-t transition-all" style="height: 100%"></div>
	            </div>
          </div>
          <div class="flex justify-between px-2 pt-4 text-[10px] text-slate-500 uppercase tracking-widest font-bold">
            <span>Week 1</span>
            <span>Week 2</span>
            <span>Week 3</span>
            <span>Week 4</span>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-8">
            <h3 class="text-lg font-bold dark:text-white">Recent Records</h3>
            <router-link to="/student/history" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
              View All History <ArrowRight :size="14" />
            </router-link>
          </div>
          <div class="space-y-4">
            <div v-for="record in recentRecords" :key="record.id" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-4">
                <div :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center',
                  record.status === 'PRESENT' ? 'bg-green-100 text-green-600' : 
                  record.status === 'LATE' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600'
                ]">
                  <CheckCircle2 v-if="record.status === 'PRESENT'" :size="20" />
                  <Clock v-else-if="record.status === 'LATE'" :size="20" />
                  <X v-else :size="20" />
                </div>
                <div>
                  <p class="text-sm font-bold dark:text-white">{{ record.courseName }}</p>
                  <p class="text-[10px] text-slate-500">{{ record.date }} • {{ record.timeSlot }}</p>
                </div>
              </div>
              <span :class="[
                'px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider',
                record.status === 'PRESENT' ? 'bg-green-500/10 text-green-600' : 
                record.status === 'LATE' ? 'bg-amber-500/10 text-amber-600' : 'bg-red-500/10 text-red-600'
              ]">
                {{ record.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-8">
	        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col">
	          <h3 class="text-lg font-bold mb-6 dark:text-white">Status Breakdown</h3>
	          <div class="space-y-6 flex-1">
	            <div v-for="item in statusBreakdown" :key="item.label">
	              <div class="flex justify-between text-sm mb-2">
	                <span class="text-slate-600 dark:text-slate-400">{{ item.label }}</span>
	                <span class="font-bold dark:text-white">{{ item.val }}%</span>
	              </div>
              <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div :class="[item.color, 'h-full rounded-full']" :style="{ width: `${item.val}%` }"></div>
              </div>
            </div>
          </div>
          <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3 text-sm text-slate-500 italic">
              <Info class="text-primary" :size="16" />
              Provide documentation for unexcused absences within 48 hours.
            </div>
          </div>
        </div>

        <div class="bg-primary/10 dark:bg-primary/5 p-8 rounded-3xl border border-primary/20 relative overflow-hidden group">
          <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-primary/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
          <h3 class="text-lg font-bold text-primary mb-2">Need Help?</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Check our student guide for attendance policies and technical support.</p>
          <button class="w-full bg-primary text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-blue-600 transition-all shadow-lg shadow-primary/20">
            View Study Planner
            <ChevronRight :size="18" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
