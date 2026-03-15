import api from './api'

const formatError = (error) => {
  if (error.response?.status === 422) {
    return {
      message: error.response.data?.message || 'Validation failed.',
      errors: error.response.data?.errors || {},
    }
  }

  return {
    message: error.response?.data?.message || error.message || 'Attendance request failed.',
    errors: {},
  }
}

export const attendanceService = {
  async markAttendance(payload) {
    try {
      const response = await api.post('/attendance/mark', payload)
      return response.data
    } catch (error) {
      throw formatError(error)
    }
  },

  async testTelegram() {
    try {
      const response = await api.post('/test/telegram', { isTest: true })
      return response.data
    } catch (error) {
      throw formatError(error)
    }
  },

  async processAbsences(sessionId, sendTelegram = true) {
    try {
      const response = await api.post('/attendance/process-absences', {
        session_id: sessionId,
        send_telegram: sendTelegram,
      })
      return response.data
    } catch (error) {
      throw formatError(error)
    }
  },
}
