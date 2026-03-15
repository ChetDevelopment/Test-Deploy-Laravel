import axios from 'axios'
import { clearToken, getStudentSession, getToken, setToken, clearStoredUser, clearStoredRole, clearStudentSession } from './storage'

const notifyUnauthorized = () => {
  if (typeof window === 'undefined') return
  window.dispatchEvent(new CustomEvent('auth:unauthorized'))
}

const envBaseUrl = import.meta.env.VITE_API_BASE_URL
const normalizedBaseUrl = typeof envBaseUrl === 'string' && envBaseUrl.trim()
  ? envBaseUrl.replace(/\/+$/, '')
  : '/api'

const api = axios.create({
  baseURL: normalizedBaseUrl,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

const refreshClient = axios.create({
  baseURL: normalizedBaseUrl,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

let refreshPromise = null

api.interceptors.request.use((config) => {
  const token = getToken()
  const studentSession = getStudentSession()

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  if (studentSession) {
    config.headers['X-Student-Session'] = studentSession
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const apiError = error.response?.data?.error

    if (status === 401) {
      const originalRequest = error.config
      const token = getToken()

      if (apiError === 'token_expired' && token && originalRequest && !originalRequest._retry) {
        originalRequest._retry = true

        refreshPromise ??= refreshClient
          .post('/auth/refresh', null, { headers: { Authorization: `Bearer ${token}` } })
          .then((res) => {
            const newToken = res?.data?.token
            if (!newToken) {
              throw new Error('Token refresh did not return a token')
            }
            return newToken
          })
          .finally(() => {
            refreshPromise = null
          })

        return refreshPromise
          .then((newToken) => {
            setToken(newToken)
            originalRequest.headers ??= {}
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            return api(originalRequest)
          })
          .catch((refreshError) => {
            clearToken()
            clearStoredUser()
            clearStoredRole()
            clearStudentSession()
            notifyUnauthorized()
            return Promise.reject(refreshError)
          })
      }

      clearToken()
      clearStoredUser()
      clearStoredRole()
      clearStudentSession()
      notifyUnauthorized()
    }

    return Promise.reject(error)
  }
)

export default api

const toAttendanceStatus = (status) => {
  const normalized = String(status ?? '').trim().toLowerCase()
  if (normalized === 'present') return 'PRESENT'
  if (normalized === 'late') return 'LATE'
  if (normalized === 'absent') return 'ABSENT'
  if (normalized === 'pending') return 'PENDING'
  return 'PENDING'
}

const toDateString = (value) => {
  const date = value ? new Date(value) : null
  if (!date || Number.isNaN(date.getTime())) return ''
  return date.toISOString().slice(0, 10)
}

// Student attendance history
export const fetchAttendanceHistory = async () => {
  try {
    const response = await api.get('/student/attendance/history')
    const records = Array.isArray(response.data) ? response.data : []

    return records.map((record) => ({
      id: String(record?.id ?? ''),
      studentId: 'N/A',
      status: toAttendanceStatus(record?.status),
      timestamp: record?.check_in_time ?? '',
      date: toDateString(record?.check_in_time ?? record?.created_at),
      timeSlot: record?.session_start && record?.session_end ? `${record.session_start} - ${record.session_end}` : '',
      courseName: record?.course_name ?? record?.courseName ?? 'Session',
    }))
  } catch (error) {
    console.error('Failed to fetch attendance history:', error)
    throw error
  }
}

export const fetchStudentDashboardStats = async () => {
  const response = await api.get('/student/dashboard/stats')
  return response.data
}

// Student check-in
export const checkIn = async (data) => {
  try {
    const payload = {
      sessionId: data?.sessionId ?? data?.session_id ?? data?.sessionID,
      method: data?.method,
      photo: data?.photo ?? data?.photoProof,
      qrCode: data?.qrCode ?? data?.qr_data ?? data?.qrData,
    }

    const response = await api.post('/student/attendance/check-in', payload)
    return response.data
  } catch (error) {
    console.error('Failed to check in:', error)
    throw error
  }
}

// Submit manual attendance request
export const submitManualAttendanceRequest = async (data) => {
  try {
    const sessionId = data?.sessionId ?? data?.session_id ?? data?.sessionID
    const reason = data?.reason

    const response = await api.post('/student/attendance/request', {
      sessionId,
      reason,
    })
    return response.data
  } catch (error) {
    console.error('Failed to submit manual attendance request:', error)
    throw error
  }
}
