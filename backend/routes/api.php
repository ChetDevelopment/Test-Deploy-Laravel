<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\EducationDashboardController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AttendanceRecordController as AdminAttendanceRecordController;
use App\Http\Controllers\Admin\RedisTestController;
use App\Http\Controllers\Admin\SystemMaintenanceController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AbsenceManagementController;

// Public health check endpoints (no authentication required)
Route::get('/health', [SystemHealthController::class, 'health']);
Route::get('/ping', [SystemHealthController::class, 'ping']);

Route::prefix('admin')->middleware(['auth.jwt', 'role:admin,teacher,education'])->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/overview', [DashboardController::class, 'getOverview']);
    Route::get('/dashboard/late-students', [DashboardController::class, 'lateStudents']);
    Route::get('/dashboard/offsite-students', [DashboardController::class, 'offsiteStudentsToday']);
    Route::get('/dashboard/notifications', [DashboardController::class, 'recentNotifications']);
    Route::get('/dashboard/active-session', [DashboardController::class, 'activeSession']);

    // Absence Management Routes
    Route::get('/absences/stats', [AbsenceManagementController::class, 'stats']);
    Route::get('/absences', [AbsenceManagementController::class, 'index']);
    Route::get('/absences/{id}', [AbsenceManagementController::class, 'show']);
    Route::put('/absences/{id}/reason', [AbsenceManagementController::class, 'updateReason']);
    Route::post('/absences/{id}/comment', [AbsenceManagementController::class, 'addComment']);
    Route::patch('/absences/{id}/status', [AbsenceManagementController::class, 'updateStatus']);
    Route::post('/absences/{id}/follow-up', [AbsenceManagementController::class, 'addFollowUp']);
    Route::get('/absences/student/{studentId}/history', [AbsenceManagementController::class, 'getHistory']);
    Route::post('/absences/bulk-status', [AbsenceManagementController::class, 'bulkUpdateStatus']);
});

// Student Biometric Attendance Routes (public for self-service kiosk)
Route::prefix('student/attendance')->group(function () {
    // Biometric scanning endpoints
    Route::post('/card-scan', [StudentAttendanceController::class, 'scanCard']);
    Route::post('/fingerprint-scan', [StudentAttendanceController::class, 'scanFingerprint']);
    Route::post('/validate-biometric', [StudentAttendanceController::class, 'validateBiometric']);
    
    // Student info after scan
    Route::post('/student-info', [StudentAttendanceController::class, 'getStudentInfo']);
    
    // Biometric history and status
    Route::get('/biometric-history', [StudentAttendanceController::class, 'getBiometricHistory']);
    Route::get('/biometric-status', [StudentAttendanceController::class, 'getBiometricStatus']);
    
    // Enrollment endpoints (admin/teacher only)
    Route::middleware('auth.jwt')->group(function () {
        Route::post('/enroll-card', [StudentAttendanceController::class, 'enrollCard']);
        Route::post('/enroll-fingerprint', [StudentAttendanceController::class, 'enrollFingerprint']);
        Route::post('/remove-biometric', [StudentAttendanceController::class, 'removeBiometric']);
    });
});

// Student Dashboard Routes
Route::prefix('student')->middleware(['auth.jwt', 'role:student'])->group(function () {
    // Dashboard stats
    Route::get('/dashboard/stats', [StudentDashboardController::class, 'getStats']);
    
    // Attendance history
    Route::get('/attendance/history', [StudentDashboardController::class, 'getHistory']);
    
    // Check-in endpoints
    Route::post('/attendance/check-in', [StudentDashboardController::class, 'checkIn']);
    Route::post('/attendance/request', [StudentDashboardController::class, 'requestManual']);
});

Route::prefix('admin')->middleware(['auth.jwt', 'role:admin,teacher,education'])->group(function () {

    // Users + Roles
    Route::apiResource('users', UserController::class);

    // Students
    Route::post('students/bulk', [StudentController::class, 'bulkStore']);
    Route::apiResource('students', StudentController::class);
    Route::post('students/{student}/photo', [StudentController::class, 'uploadPhoto']);
    Route::get('redis/test/cache', [RedisTestController::class, 'cacheTest']);
    Route::post('redis/test/queue', [RedisTestController::class, 'queueTest']);
    Route::get('redis/test/queue/status', [RedisTestController::class, 'queueStatus']);

    // Classes
    Route::apiResource('classes', ClassController::class);

    // Academic Year
    Route::apiResource('academic-years', AcademicYearController::class);

    // Roles
    Route::get('roles', [RoleController::class, 'index']);

    // Attendance records + sessions (admin control panel)
    Route::get('attendance-records', [AdminAttendanceRecordController::class, 'index']);
    Route::patch('attendance-records/{attendanceRecord}', [AdminAttendanceRecordController::class, 'update']);
    Route::post('attendance-records/{attendanceRecord}/unlock', [AdminAttendanceRecordController::class, 'unlock']);
    Route::post('attendance-records/manual-correction', [AdminAttendanceRecordController::class, 'manualCorrection']);
    Route::get('attendance-sessions', [AdminAttendanceRecordController::class, 'sessions']);

    // System maintenance (admin dashboard settings page)
    Route::post('system/clear-cache', [SystemMaintenanceController::class, 'clearCache']);
    Route::get('system/export-config', [SystemMaintenanceController::class, 'exportConfig']);
});

Route::prefix('auth')->group(function () {
    Route::match(['get', 'post'], '/register', [AuthController::class, 'register']);
    Route::match(['get', 'post'], '/login', [AuthController::class, 'login']);

    Route::middleware('auth.jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Notifications Routes
Route::middleware('auth.jwt')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

Route::middleware('auth.jwt')->apiResource('attendances', AttendanceController::class);
Route::middleware('auth.jwt')->group(function () {
    Route::post('/attendance', [AttendanceRecordController::class, 'store']);
    Route::post('/attendance/mark-present/{student}/{session}', [AttendanceRecordController::class, 'markPresent']);
    Route::post('/attendance/mark-absent/{student}/{session}', [AttendanceRecordController::class, 'markAbsent']);
    Route::post('/attendance/mark-late/{student}/{session}', [AttendanceRecordController::class, 'markLate']);
    Route::post('/attendance/mark', [AttendanceRecordController::class, 'markAttendance']);
    Route::get('/teacher/{teacher}/activities', [AttendanceRecordController::class, 'activityLog']);

    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::post('/user/profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/user/profile/avatar', [UserProfileController::class, 'uploadAvatar']);
    Route::post('/user/settings', [UserProfileController::class, 'updateSettings']);

    Route::get('/dashboard/stats', [EducationDashboardController::class, 'stats']);
    Route::get('/dashboard/overview', [EducationDashboardController::class, 'getOverview']);
    Route::get('/students/absent-today', [EducationDashboardController::class, 'absentToday']);
    Route::get('/students/all-absent', [EducationDashboardController::class, 'allAbsent']);
    Route::get('/students/risk', [EducationDashboardController::class, 'riskStudents']);
    Route::get('/reports/class', [EducationDashboardController::class, 'classReports']);
    Route::get('/reports/trends', [EducationDashboardController::class, 'trends']);
    Route::get('/attendance/detail/{id}', [EducationDashboardController::class, 'attendanceDetail']);
    Route::post('/attendance/follow-up', [EducationDashboardController::class, 'saveFollowUp']);
    Route::post('/attendance/alert', [EducationDashboardController::class, 'sendAlert']);
    Route::post('/attendance/process-absences', [EducationDashboardController::class, 'processSessionAbsences']);
    Route::get('/attendance/session-notifications', [EducationDashboardController::class, 'getSessionNotificationStatus']);
    Route::post('/attendance/retry-notifications', [EducationDashboardController::class, 'retryFailedNotifications']);
    Route::post('/test/telegram', [EducationDashboardController::class, 'testTelegramNotification']);
    Route::post('/education/absence-reason', [EducationDashboardController::class, 'submitAbsenceReason']);
    Route::get('/education/absence-details', [EducationDashboardController::class, 'getAbsenceDetails']);

    Route::post('/debug/reset-db', [DebugController::class, 'resetDb']);

    Route::middleware('role:teacher,admin')->group(function () {
        Route::get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard']);
        Route::get('/teacher/schedule', [TeacherPortalController::class, 'schedule']);
        Route::get('/teacher/justifications', [TeacherPortalController::class, 'justifications']);
        Route::get('/teacher/history', [TeacherPortalController::class, 'history']);
        Route::get('/teacher/students', [TeacherPortalController::class, 'students']);
        Route::get('/teacher/notifications', [TeacherPortalController::class, 'notifications']);
    });
});

// Session Management Routes
// Public routes for checking current session
Route::prefix('sessions')->group(function () {
    Route::get('/current', [SessionController::class, 'current']);
    Route::get('/next', [SessionController::class, 'next']);
    Route::get('/active', [SessionController::class, 'active']);
    Route::get('/for-date', [SessionController::class, 'forDate']);
    Route::get('/config', [SessionController::class, 'config']);
});

// Admin-only session management routes
Route::prefix('admin')->middleware(['auth.jwt', 'role:admin,teacher,education'])->group(function () {
    Route::apiResource('sessions', SessionController::class);
    Route::post('/sessions/initialize', [SessionController::class, 'initialize']);
    Route::post('/sessions/{id}/toggle', [SessionController::class, 'toggle']);
    Route::get('/sessions/{id}/validate', [SessionController::class, 'validateTime']);
    Route::post('/sessions/{id}/can-checkin', [SessionController::class, 'canCheckIn']);
    Route::post('/sessions/{id}/check-late', [SessionController::class, 'checkLate']);
});

// Report Routes
// Public routes for basic reports (with authentication for full access)
Route::prefix('reports')->middleware('auth.jwt')->group(function () {
    // Student Reports
    Route::get('/student/{id}', [ReportController::class, 'getStudentReport']);
    Route::get('/student/{id}/month/{month}/year/{year}', [ReportController::class, 'getStudentReportByMonth']);
    Route::get('/student/{id}/year/{year}', [ReportController::class, 'getStudentReportByYear']);
    
    // Class Reports
    Route::get('/class/{id}', [ReportController::class, 'getClassReport']);
    Route::get('/class/{id}/month/{month}/year/{year}', [ReportController::class, 'getClassMonthlySummary']);
    Route::get('/class/{id}/range', [ReportController::class, 'getClassReportByDateRange']);
    
    // Attendance Records
    Route::get('/attendance', [ReportController::class, 'getAttendance']);
    
    // Excel Exports
    Route::get('/export/student/{id}', [ReportController::class, 'exportStudentReport']);
    Route::get('/export/class/{id}', [ReportController::class, 'exportClassReport']);
    Route::get('/export/range', [ReportController::class, 'exportByDateRange']);
    
    // Cache Management
    Route::post('/clear-cache', [ReportController::class, 'clearCache']);
});

// Prediction Routes
Route::prefix('predictions')->middleware('auth.jwt')->group(function () {
    // At-risk students
    Route::get('/at-risk', [PredictionController::class, 'getAtRiskStudents']);
    
    // Individual student prediction
    Route::get('/student/{id}', [PredictionController::class, 'getStudentPrediction']);
    
    // Overall system insights
    Route::get('/insights', [PredictionController::class, 'getInsights']);
    
    // Weekly prediction
    Route::get('/weekly', [PredictionController::class, 'getWeeklyPrediction']);
    
    // Historical data analysis
    Route::get('/historical', [PredictionController::class, 'getHistoricalData']);
    
    // Clear cache (admin only)
    Route::post('/clear-cache', [PredictionController::class, 'clearCache']);
});
