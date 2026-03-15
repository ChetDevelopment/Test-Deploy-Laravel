<?php

namespace App\Http\Controllers;

use App\Models\AttendanceFollowUp;
use App\Models\AbsenceNotification;
use App\Models\User;
use App\Jobs\SendTelegramNotificationJob;
use App\Services\TelegramService;
use App\Services\AbsenceNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EducationDashboardController extends Controller
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly AbsenceNotificationService $absenceNotificationService
    ) {
    }

    /**
     * Get combined dashboard overview in a single request
     * Major performance optimization to reduce frontend round trips
     */
    public function getOverview()
    {
        $cacheKey = 'education_dashboard_overview';
        $cacheTtl = 60; // 1 minute for the combined overview

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return response()->json([
                'stats' => $this->getStatsData(),
                'absentToday' => $this->getAbsentTodayData(),
                'allAbsent' => $this->getAllAbsentData(),
                'riskStudents' => $this->getRiskStudentsData(),
                'classReports' => $this->getClassReportsData(),
                'trends' => $this->getTrendsData(),
            ]);
        });
    }

    private function getStatsData()
    {
        $todayStart = Carbon::today();
        $todayEnd = $todayStart->copy()->endOfDay();

        $stats = DB::table('attendance_records as ar')
            ->whereBetween('ar.created_at', [$todayStart, $todayEnd])
            ->selectRaw(
                "SUM(CASE WHEN ar.status = 'Absent' THEN 1 ELSE 0 END) as absent_today,
                 SUM(CASE WHEN ar.status = 'Late' THEN 1 ELSE 0 END) as late_today"
            )
            ->first();

        $highRisk = DB::table('attendance_records')
            ->select('student_id')
            ->where('status', 'Absent')
            ->where('created_at', '>=', $todayStart->copy()->subDays(30))
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 3')
            ->count();

        $pendingFollowUp = DB::table('v_admin_attendance_enriched as va')
            ->leftJoin('v_attendance_latest_follow_up as lf', 'lf.attendance_record_id', '=', 'va.attendance_id')
            ->where('va.status', 'Absent')
            ->where(function ($query): void {
                $query->whereNull('lf.follow_up_id')->orWhere('lf.resolved', false);
            })
            ->count();

        return [
            'absentToday' => (int) ($stats->absent_today ?? 0),
            'lateToday' => (int) ($stats->late_today ?? 0),
            'highRisk' => $highRisk,
            'pendingFollowUp' => $pendingFollowUp,
        ];
    }

    private function getAbsentTodayData()
    {
        $todayStart = Carbon::today();
        $todayEnd = $todayStart->copy()->endOfDay();

        return DB::table('v_admin_attendance_enriched as va')
            ->leftJoin('v_attendance_latest_follow_up as lf', 'lf.attendance_record_id', '=', 'va.attendance_id')
            ->where('va.status', 'Absent')
            ->whereBetween('va.created_at', [$todayStart, $todayEnd])
            ->select([
                'va.attendance_id as attendance_id',
                'va.student_name as name',
                'va.class_name as class',
                DB::raw('COALESCE(lf.resolved, 0) as resolved'),
            ])
            ->orderByDesc('va.attendance_id')
            ->get();
    }

    private function getAllAbsentData()
    {
        return DB::table('v_admin_attendance_enriched as va')
            ->leftJoin('v_attendance_latest_follow_up as lf', 'lf.attendance_record_id', '=', 'va.attendance_id')
            ->where('va.status', 'Absent')
            ->select([
                'va.attendance_id as attendance_id',
                'va.created_date as date',
                'va.student_name as name',
                'va.class_name as class',
                DB::raw("COALESCE(lf.reason, 'Unknown') as reason"),
                DB::raw('COALESCE(lf.resolved, 0) as resolved'),
            ])
            ->orderByDesc('va.created_at')
            ->limit(200)
            ->get();
    }

    private function getRiskStudentsData()
    {
        return DB::table('v_admin_attendance_enriched as va')
            ->where('va.status', 'Absent')
            ->where('va.created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('va.student_id', 'va.student_name', 'va.class_name')
            ->havingRaw('COUNT(*) >= 3')
            ->select([
                'va.student_name as name',
                'va.class_name as class',
                DB::raw('COUNT(*) as absence_count'),
                DB::raw('MAX(va.attendance_id) as latest_attendance_id'),
            ])
            ->orderByDesc('absence_count')
            ->limit(20)
            ->get();
    }

    private function getClassReportsData()
    {
        return DB::table('v_admin_attendance_enriched as va')
            ->groupBy('va.class_name')
            ->select([
                'va.class_name as class',
                DB::raw("SUM(CASE WHEN va.status = 'Present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN va.status = 'Absent' THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN va.status = 'Late' THEN 1 ELSE 0 END) as late_count"),
            ])
            ->orderBy('class')
            ->get();
    }

    private function getTrendsData()
    {
        $start = Carbon::today()->startOfMonth();
        $end = Carbon::today()->copy()->endOfMonth();

        $rows = DB::table('attendance_records')
            ->where('status', 'Absent')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("FLOOR((DAY(created_at)-1)/7)+1 as week_no, COUNT(*) as value")
            ->groupBy('week_no')
            ->pluck('value', 'week_no');

        return collect(range(1, 4))->map(function (int $week) use ($rows) {
            return [
                'name' => 'W' . $week,
                'value' => (int) ($rows[$week] ?? 0),
            ];
        })->values();
    }

    /**
     * Get dashboard statistics with caching (5 minutes)
     */
    public function stats()
    {
        return Cache::remember('education_dashboard_stats', 300, function () {
            return response()->json($this->getStatsData());
        });
    }

    /**
     * Get today's absent students with caching (1 minute)
     */
    public function absentToday()
    {
        return Cache::remember('education_absent_today', 60, function () {
            return response()->json($this->getAbsentTodayData());
        });
    }

    /**
     * Get all absent students with caching (5 minutes)
     */
    public function allAbsent()
    {
        return Cache::remember('education_all_absent', 300, function () {
            return response()->json($this->getAllAbsentData());
        });
    }

    /**
     * Get high-risk students with caching (5 minutes)
     */
    public function riskStudents()
    {
        return Cache::remember('education_risk_students', 300, function () {
            return response()->json($this->getRiskStudentsData());
        });
    }

    /**
     * Get class reports with caching (5 minutes)
     */
    public function classReports()
    {
        return Cache::remember('education_class_reports', 300, function () {
            return response()->json($this->getClassReportsData());
        });
    }

    /**
     * Get attendance trends with caching (5 minutes)
     */
    public function trends()
    {
        return Cache::remember('education_trends', 300, function () {
            return response()->json($this->getTrendsData());
        });
    }

    public function attendanceDetail(int $id)
    {
        $attendance = DB::table('v_admin_attendance_enriched as va')
            ->where('va.attendance_id', $id)
            ->select([
                'va.attendance_id as id',
                'va.created_date as date',
                'va.student_name as name',
                'va.class_name as class',
                'va.parent_number as contact_info',
            ])
            ->first();

        if (! $attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $followUps = DB::table('attendance_follow_ups as af')
            ->leftJoin('users as u', 'u.id', '=', 'af.updated_by')
            ->where('af.attendance_record_id', $id)
            ->orderByDesc('af.created_at')
            ->selectRaw(
                "COALESCE(u.name, 'System') as updated_by,
                af.status,
                af.comment,
                af.note,
                af.resolved,
                af.is_excused,
                af.reason,
                af.created_at as timestamp"
            )
            ->get();

        $latest = $followUps->first();

        return response()->json([
            'id' => $attendance->id,
            'name' => $attendance->name,
            'class' => $attendance->class,
            'date' => $attendance->date,
            'contact_info' => $attendance->contact_info,
            'reason' => $latest->reason ?? '',
            'is_excused' => (int) ($latest->is_excused ?? 0),
            'followUps' => $followUps,
        ]);
    }

    public function saveFollowUp(Request $request)
    {
        $validated = $request->validate([
            'attendanceId' => ['required', 'integer', 'exists:attendance_records,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:100'],
            'resolved' => ['nullable', 'boolean'],
            'isExcused' => ['nullable', 'boolean'],
        ]);

        $followUp = AttendanceFollowUp::create([
            'attendance_record_id' => $validated['attendanceId'],
            'updated_by' => optional($request->user())->id,
            'reason' => $validated['reason'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'note' => $validated['note'] ?? null,
            'status' => $validated['status'] ?? 'Not Contacted',
            'resolved' => (bool) ($validated['resolved'] ?? false),
            'is_excused' => (bool) ($validated['isExcused'] ?? false),
        ]);

        return response()->json([
            'message' => 'Follow-up saved successfully',
            'id' => $followUp->id,
        ], 201);
    }

    public function sendAlert(Request $request)
    {
        $request->validate([
            'attendanceId' => ['nullable', 'integer'],
            'studentName' => ['nullable', 'string'],
            'studentId' => ['nullable', 'string'],
            'className' => ['nullable', 'string'],
            'date' => ['nullable', 'string'],
            'sessionTime' => ['nullable', 'string'],
            'isTest' => ['nullable', 'boolean'],
        ]);

        // If it's a test message
        if ($request->boolean('isTest')) {
            $result = $this->telegramService->sendTestMessage();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? 'Test message sent successfully to Telegram'
                    : 'Failed to send test message: ' . ($result['error'] ?? 'Unknown error'),
            ], $result['success'] ? 200 : 400);
        }

        // Send absence alert via background job for better performance
        $attendanceId = $request->input('attendanceId');
        
        // If we have an attendance record, use the optimized job
        if ($attendanceId) {
            $attendance = DB::table('attendance_records')->where('id', $attendanceId)->first();
            if ($attendance) {
                SendTelegramNotificationJob::dispatch(
                    $attendance->student_id,
                    $attendance->session_id,
                    $attendance->id
                )->onQueue('telegram_notifications');

                return response()->json([
                    'success' => true,
                    'message' => 'Alert queued for delivery',
                ], 200);
            }
        }

        // Fallback for manual alerts without specific attendance ID
        $studentName = $request->input('studentName', 'Unknown Student');
        $studentIdStr = $request->input('studentId', 'N/A');
        $className = $request->input('className', 'General');
        $date = $request->input('date', now()->format('Y-m-d'));
        $sessionTime = $request->input('sessionTime', 'N/A');

        $result = $this->telegramService->sendAbsenceAlert(
            $studentName,
            $studentIdStr,
            $className,
            $date,
            $sessionTime
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Alert sent successfully'
                : 'Failed to send alert: ' . ($result['error'] ?? 'Unknown error'),
            'telegram_message_id' => $result['message_id'] ?? null,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Process absences for a completed session.
     * This endpoint should be called after a teacher submits/closes attendance for a session.
     * It will detect absent students and send notifications.
     */
    public function processSessionAbsences(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'send_telegram' => 'boolean',
            'send_education_api' => 'boolean',
        ]);

        $sessionId = $request->input('session_id');
        $sendTelegram = $request->input('send_telegram', true);
        $sendEducationApi = $request->input('send_education_api', true);

        $result = $this->absenceNotificationService->processSessionAbsences(
            $sessionId,
            $sendTelegram,
            $sendEducationApi
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get notification status for a session.
     */
    public function getSessionNotificationStatus(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
        ]);

        $sessionId = $request->input('session_id');
        $result = $this->absenceNotificationService->getSessionNotificationStatus($sessionId);

        return response()->json($result);
    }

    /**
     * Retry failed notifications for a session.
     */
    public function retryFailedNotifications(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'notification_types' => 'nullable|array',
            'notification_types.*' => 'in:telegram,education_api',
        ]);

        $sessionId = $request->input('session_id');
        $notificationTypes = $request->input('notification_types', ['telegram', 'education_api']);

        $result = $this->absenceNotificationService->retryFailedNotifications($sessionId, $notificationTypes);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Test Telegram notification.
     */
    public function testTelegramNotification(Request $request)
    {
        $request->validate([
            'student_name' => 'nullable|string',
        ]);

        $studentName = $request->input('student_name', 'Test Student');

        $result = $this->telegramService->sendAbsenceAlert(
            $studentName,
            'TEST001',
            'Test Class',
            now()->format('Y-m-d'),
            now()->format('H:i')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Test notification sent successfully! Message ID: ' . ($result['message_id'] ?? 'N/A')
                : 'Failed to send test notification: ' . ($result['error'] ?? 'Unknown error'),
            'telegram_result' => $result,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Submit absence reason from Education Department.
     * Called when Education submits reason via web app.
     */
    public function submitAbsenceReason(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:absence_notifications,id',
            'reason' => 'required|string|min:5',
        ]);

        $notification = AbsenceNotification::findOrFail($request->input('notification_id'));
        $reason = $request->input('reason');
        $userId = $request->user()->id ?? null;

        // Update the notification with the reason
        $notification->update([
            'absence_reason' => $reason,
            'reason_submitted_by' => $userId,
            'reason_submitted_at' => now(),
        ]);

        // Get the attendance record and notify teacher
        $attendanceRecord = $notification->attendanceRecord;
        $student = $notification->student;
        $session = $notification->session;

        // Notify teacher about the reason (via Telegram to the teacher or update attendance record)
        if ($attendanceRecord) {
            // Update attendance record with justification
            $attendanceRecord->update([
                'justification' => $reason,
                'justified_at' => now(),
                'justified_by' => $userId,
            ]);

            // Send notification back to teacher (using the session's teacher)
            if ($session && $session->teacher_id) {
                $teacher = User::find($session->teacher_id);
                if ($teacher && $teacher->telegram_chat_id) {
                    $this->telegramService->sendMessage(
                        "✅ <b>Absence Justification Received</b>\n\n" .
                        "👤 <b>Student:</b> " . ($student->fullname ?? 'Unknown') . "\n" .
                        "📝 <b>Reason:</b> {$reason}\n" .
                        "📅 <b>Date:</b> " . ($attendanceRecord->date ?? 'N/A')
                    );
                }
            }

            // Also update attendance follow-up
            \App\Models\AttendanceFollowUp::updateOrCreate(
                [
                    'attendance_record_id' => $attendanceRecord->id,
                    'follow_up_date' => now()->toDateString(),
                ],
                [
                    'reason' => $reason,
                    'status' => 'justified',
                ]
            );
        }

        Log::info('Absence reason submitted by Education Department', [
            'notification_id' => $notification->id,
            'student_id' => $notification->student_id,
            'reason' => $reason,
            'submitted_by' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absence reason submitted successfully',
            'data' => [
                'notification_id' => $notification->id,
                'student_name' => $student->fullname ?? $student->username ?? 'Unknown',
                'reason' => $reason,
                'submitted_at' => $notification->reason_submitted_at,
            ],
        ]);
    }

    /**
     * Get absence details for Education to view.
     */
    public function getAbsenceDetails(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:absence_notifications,id',
        ]);

        $notification = AbsenceNotification::with(['student', 'session', 'attendanceRecord'])
            ->findOrFail($request->input('notification_id'));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'student' => $notification->student ? [
                    'id' => $notification->student->id,
                    'name' => $notification->student->fullname ?? $notification->student->username,
                    'class' => $notification->student->class,
                    'username' => $notification->student->username,
                ] : null,
                'session' => $notification->session ? [
                    'id' => $notification->session->id,
                    'name' => $notification->session->name,
                    'date' => $notification->session->date,
                    'start_time' => $notification->session->start_time,
                    'end_time' => $notification->session->end_time,
                ] : null,
                'attendance_record' => $notification->attendanceRecord ? [
                    'id' => $notification->attendanceRecord->id,
                    'status' => $notification->attendanceRecord->status,
                    'date' => $notification->attendanceRecord->date,
                ] : null,
                'absence_reason' => $notification->absence_reason,
                'reason_submitted_at' => $notification->reason_submitted_at,
            ],
        ]);
    }
}
