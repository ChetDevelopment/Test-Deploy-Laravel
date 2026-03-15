import { createRouter, createWebHistory } from 'vue-router'
import { getUserRole, hasSession } from '../services/auth'

const routes = [
  {
    path: '/',
    redirect: () => {
      const role = getUserRole()
      if (role === 'teacher') return '/teacher/dashboard'
      if (role === 'education') return '/education/dashboard'
      if (role === 'student') return '/student/dashboard'
      if (role === 'admin') return '/dashboard'
      return '/dashboard'
    },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/teacher/dashboard',
    name: 'teacher-dashboard',
    component: () => import('../pages/TeacherDashboardPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/education/dashboard',
    name: 'education-dashboard',
    component: () => import('../pages/EducationDashboardPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/student',
    component: () => import('../components/Student/StudentLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: 'dashboard',
      },
      {
        path: 'dashboard',
        name: 'student-dashboard',
        component: () => import('../components/Student/DashboardStudent.vue'),
      },
      {
        path: 'attendance',
        name: 'student-attendance',
        component: () => import('../components/Student/AttendanceStudent.vue'),
      },
      {
        path: 'biometric-scan',
        name: 'student-biometric-scan',
        component: () => import('../components/Student/BiometricScan.vue'),
      },
      {
        path: 'history',
        name: 'student-history',
        component: () => import('../components/Student/HistoryStudent.vue'),
      },
      {
        path: 'settings',
        name: 'student-settings',
        component: () => import('../components/Student/SettingsStudent.vue'),
      },
    ],
  },
  {
    path: '/reports',
    name: 'reports',
    component: () => import('../pages/ReportsPage.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const loggedIn = hasSession()
  const role = getUserRole()

  if (to.meta.requiresAuth && !loggedIn) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && loggedIn) {
    if (role === 'teacher') return { name: 'teacher-dashboard' }
    if (role === 'education') return { name: 'education-dashboard' }
    if (role === 'student') return { name: 'student-dashboard' }
    return { name: 'dashboard' }
  }

  if (to.name === 'dashboard' && role === 'teacher') {
    return { name: 'teacher-dashboard' }
  }

  if (to.name === 'dashboard' && role === 'education') {
    return { name: 'education-dashboard' }
  }

  if (to.name === 'dashboard' && role === 'student') {
    return { name: 'student-dashboard' }
  }

  if (to.name === 'teacher-dashboard' && role && role !== 'teacher') {
    return { name: 'dashboard' }
  }

  if (to.name === 'education-dashboard' && role && role !== 'education') {
    return { name: 'dashboard' }
  }

  if ((to.name === 'student-dashboard' || to.name === 'student-dashboard-legacy') && role && role !== 'student') {
    return { name: 'dashboard' }
  }

  // Handle student child routes
  if (to.path.startsWith('/student/') && role && role !== 'student') {
    return { name: 'dashboard' }
  }

  return true
})

export default router
