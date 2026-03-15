import api from './api'

const formatError = (error) => {
  if (error.response?.status === 422) {
    return {
      message: error.response.data?.message || 'Validation failed.',
      errors: error.response.data?.errors || {},
    }
  }

  return {
    message: error.response?.data?.message || error.message || 'Request failed.',
    errors: {},
  }
}

const unwrapSessions = (payload) => {
  if (Array.isArray(payload)) return payload
  if (payload && Array.isArray(payload.data)) return payload.data
  if (payload?.data && Array.isArray(payload.data.data)) return payload.data.data
  return []
}

export const sessionAdminService = {
  async list() {
    try {
      const response = await api.get('/admin/sessions')
      return unwrapSessions(response.data)
    } catch (error) {
      throw formatError(error)
    }
  },

  async update(id, payload) {
    try {
      const response = await api.put(`/admin/sessions/${id}`, payload)
      return response.data
    } catch (error) {
      throw formatError(error)
    }
  },
}

