import api from './api'

const memoryCache = new Map()
const pendingRequests = new Map()

const toError = (error) => {
  if (error.response?.data?.message) return error.response.data.message
  if (error.message) return error.message
  return 'Failed to load dashboard data.'
}

const getCached = (key) => {
  const hit = memoryCache.get(key)
  if (!hit) return null
  if (Date.now() > hit.expiry) {
    memoryCache.delete(key)
    return null
  }
  return hit.data
}

const setCached = (key, data, ttlMs) => {
  memoryCache.set(key, {
    data,
    expiry: Date.now() + ttlMs,
  })
}

const fetchWithCache = async (key, url, ttlMs = 30000) => {
  const cached = getCached(key)
  if (cached) return cached

  if (pendingRequests.has(key)) {
    return pendingRequests.get(key)
  }

  const request = api.get(url)
    .then((response) => {
      setCached(key, response.data, ttlMs)
      return response.data
    })
    .finally(() => {
      pendingRequests.delete(key)
    })

  pendingRequests.set(key, request)
  return request
}

export const dashboardService = {
  async getSummary() {
    try {
      return await fetchWithCache('dashboard:summary', '/admin/dashboard/summary', 30000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getLateStudents() {
    try {
      return await fetchWithCache('dashboard:late', '/admin/dashboard/late-students', 15000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getOffsiteStudents() {
    try {
      return await fetchWithCache('dashboard:offsite', '/admin/dashboard/offsite-students', 15000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getNotifications() {
    try {
      return await fetchWithCache('dashboard:notifications', '/admin/dashboard/notifications', 10000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getActiveSession() {
    try {
      return await fetchWithCache('dashboard:active-session', '/admin/dashboard/active-session', 10000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getOverview() {
    try {
      return await fetchWithCache('dashboard:overview', '/admin/dashboard/overview', 30000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getRiskStudents() {
    try {
      // Use education endpoint with caching
      return await fetchWithCache('dashboard:risk', '/education/students/risk', 300000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },

  async getTrendData() {
    try {
      // Use education endpoint with caching
      return await fetchWithCache('dashboard:trends', '/education/reports/trends', 300000)
    } catch (error) {
      throw new Error(toError(error))
    }
  },
}
  
