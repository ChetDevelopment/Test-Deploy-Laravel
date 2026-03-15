import api from './api';
import { getOptimized } from './apiOptimized';

const reportService = {
  /**
   * Get student attendance report
   */
  getStudentReport(studentId) {
    return getOptimized(`/reports/student/${studentId}`, { cache: true, cacheTTL: 300000 });
  },

  /**
   * Get student attendance report by month
   */
  getStudentReportByMonth(studentId, month, year) {
    return getOptimized(`/reports/student/${studentId}/month/${month}/year/${year}`, { cache: true, cacheTTL: 300000 });
  },

  /**
   * Get student attendance report by year
   */
  getStudentReportByYear(studentId, year) {
    return getOptimized(`/reports/student/${studentId}/year/${year}`, { cache: true, cacheTTL: 300000 });
  },

  /**
   * Get class attendance report
   */
  getClassReport(classId) {
    return getOptimized(`/reports/class/${classId}`, { cache: true, cacheTTL: 300000 });
  },

  /**
   * Get class attendance monthly summary
   */
  getClassMonthlySummary(classId, month, year) {
    return getOptimized(`/reports/class/${classId}/month/${month}/year/${year}`, { cache: true, cacheTTL: 300000 });
  },

  /**
   * Get class attendance by date range
   */
  getClassReportByDateRange(classId, startDate, endDate) {
    return getOptimized(`/reports/class/${classId}/range`, {
      params: { start_date: startDate, end_date: endDate },
      cache: true,
      cacheTTL: 300000
    });
  },

  /**
   * Get attendance records with filters
   */
  getAttendance(params = {}) {
    return getOptimized('/reports/attendance', { params, cache: true, cacheTTL: 60000 });
  },

  /**
   * Export student attendance to Excel
   */
  exportStudentReport(studentId, options = {}) {
    const params = new URLSearchParams();
    if (options.year) params.append('year', options.year);
    if (options.month) params.append('month', options.month);
    
    const queryString = params.toString();
    const url = `/reports/export/student/${studentId}${queryString ? '?' + queryString : ''}`;
    
    return api.get(url, { responseType: 'blob' });
  },

  /**
   * Export class attendance to Excel
   */
  exportClassReport(classId, options = {}) {
    const params = new URLSearchParams();
    if (options.year) params.append('year', options.year);
    if (options.month) params.append('month', options.month);
    
    const queryString = params.toString();
    const url = `/reports/export/class/${classId}${queryString ? '?' + queryString : ''}`;
    
    return api.get(url, { responseType: 'blob' });
  },

  /**
   * Export attendance by date range
   */
  exportByDateRange(startDate, endDate, options = {}) {
    const params = new URLSearchParams({
      start_date: startDate,
      end_date: endDate
    });
    
    if (options.classId) params.append('class_id', options.classId);
    if (options.studentId) params.append('student_id', options.studentId);
    
    return api.get(`/reports/export/range?${params.toString()}`, { responseType: 'blob' });
  },

  /**
   * Clear report cache
   */
  clearCache(type, id) {
    return api.post('/reports/clear-cache', { type, id });
  }
};

export default reportService;
