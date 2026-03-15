import api from './api';

export const notificationService = {
  async getNotifications() {
    try {
      // Use a shorter timeout for notifications to avoid hanging the UI
      const response = await api.get('/notifications', { timeout: 10000 });
      return response.data;
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
      throw error;
    }
  },

  async markAsRead(notificationId) {
    try {
      const response = await api.post(`/notifications/${notificationId}/read`);
      return response.data;
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      throw error;
    }
  },

  async markAllAsRead() {
    try {
      const response = await api.post('/notifications/mark-all-read');
      return response.data;
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
      throw error;
    }
  },

  async deleteNotification(notificationId) {
    try {
      const response = await api.delete(`/notifications/${notificationId}`);
      return response.data;
    } catch (error) {
      console.error('Failed to delete notification:', error);
      throw error;
    }
  },

  async getUnreadCount() {
    try {
      const response = await api.get('/notifications/unread-count');
      return response;
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
      throw error;
    }
  }
};