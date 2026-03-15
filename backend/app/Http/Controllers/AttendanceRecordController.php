<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\AttendanceRecord;
use App\Models\Session;
use App\Models\TeacherActivity;
use App\Models\AbsenceNotification;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AttendanceRecordController extends Controller
{
    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    public function store(StoreAttendanceRequest $request)
    {
        $session = Session::find($request->session_id);
        if (!$session) {
            return response()->json([
                'message' => 'Session not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $currentTime = now()->format('H:i:s');
        if ($currentTime < $session->start_time || $currentTime > $session->end_time) {
            return response()->json([
                'message' => 'Attendance can only be submitted during session time.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (AttendanceRecord::alreadySubmitted($request->student_id, $request->session_id)) {
            return response()->json([
                'message' => 'Attendance already recorded.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $attendance = AttendanceRecord::create([
                'student_id' => $request->student_id,
                'session_id' => $request->session_id,
                'status' => $request->status,
                'location' => $request->location,
                'submitted_by' => auth()->id(),
            ]);

            $this->logTeacherActivity(
                auth()->id(),
                $attendance->student_id,
                $attendance->session_id,
                "Marked {$attendance->status}",
                $request->ip()
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to store attendance record', [
                'student_id' => $request->student_id,
                'session_id' => $request->session_id,
                'submitted_by' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to record attendance at this time.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Attendance recorded successfully',
        ], Response::HTTP_CREATED);
    }

    public function markPresent(int $student, int $session, Request $request)
    {
        return $this->markFixedStatus($student, $session, 'Present', $request);
    }

    public function markAbsent(int $student, int $session, Request $request)
    {
        $result = $this->markFixedStatus($student, $session, 'Absent', $request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $this->sendAbsenceNotification($result);

        return response()->json([
            'message' => 'Student marked as Absent successfully',
        ], Response::HTTP_CREATED);
    }

    public function markLate(int $student, int $session, Request $request)
    {
        return $this->markFixedStatus($student, $session, 'Late', $request);
    }

    public function markAttendance(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_id' => 'required|exists:sessions,id',
            'status' => 'required|in:Present,Absent,Late,Excused',
        ]);

        $attendance = AttendanceRecord::where('student_id', $validated['student_id'])
            ->where('session_id', $validated['session_id'])
            ->first();

        if ($attendance && $attendance->is_locked) {
            return response()->json([
                'message' => 'Attendance is locked and cannot be modified.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $attendance = AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'session_id' => $validated['session_id'],
                ],
                [
                    'status' => $validated['status'],
                    'submitted_by' => auth()->id(),
                ]
            );

            $this->logTeacherActivity(
                auth()->id(),
                $attendance->student_id,
                $attendance->session_id,
                "Marked {$attendance->status}",
                $request->ip()
            );

            // Send Telegram notification for absence
            if ($validated['status'] === 'Absent') {
                $this->sendAbsenceNotification($attendance);
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to mark attendance', [
                'student_id' => $validated['student_id'],
                'session_id' => $validated['session_id'],
                'status' => $validated['status'],
                'submitted_by' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to save attendance at this time.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Attendance saved successfully',
        ]);
    }

    public function activityLog(int $teacher)
    {
        $activities = TeacherActivity::where('user_id', $teacher)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($activities);
    }

    private function markFixedStatus(int $student, int $session, string $status, Request $request): AttendanceRecord|JsonResponse
    {
        if (AttendanceRecord::alreadySubmitted($student, $session)) {
            return response()->json([
                'message' => 'Attendance already recorded.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $attendance = AttendanceRecord::create([
                'student_id' => $student,
                'session_id' => $session,
                'status' => $status,
                'submitted_by' => auth()->id(),
            ]);

            $this->logTeacherActivity(
                auth()->id(),
                $attendance->student_id,
                $attendance->session_id,
                "Marked {$attendance->status}",
                $request->ip()
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to mark fixed attendance status', [
                'student_id' => $student,
                'session_id' => $session,
                'status' => $status,
                'submitted_by' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to save attendance at this time.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($status !== 'Absent') {
            return response()->json([
                'message' => "Student marked as {$status} successfully",
            ], Response::HTTP_CREATED);
        }

        return $attendance;
    }

    private function logTeacherActivity(
        ?int $userId,
        ?int $studentId,
        ?int $sessionId,
        string $action,
        ?string $ipAddress
    ): void {
        if (!$userId) {
            return;
        }

        TeacherActivity::create([
            'user_id' => $userId,
            'student_id' => $studentId,
            'session_id' => $sessionId,
            'action' => $action,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Send Telegram notification for student absence.
     */
    private function sendAbsenceNotification(AttendanceRecord $attendance): void
    {
        if (!$attendance->student_id || !$attendance->session_id) {
            return;
        }

        try {
            // Get queue connection config
            $queueConnection = config('queue.connections.sync.driver', 'sync');
            
            // If using sync queue, send directly; otherwise dispatch to queue
            if ($queueConnection === 'sync') {
                // Run synchronously for immediate feedback
                $telegramService = app(TelegramService::class);
                $student = $attendance->student;
                $session = $attendance->session;
                
                if ($student && $session) {
                    $result = $telegramService->sendAbsenceAlert(
                        $student->fullname ?? $student->username ?? 'Unknown',
                        $student->username ?? (string) $student->id,
                        $student->class ?? 'Unknown Class',
                        $attendance->date ?? now()->toDateString(),
                        "{$session->start_time} - {$session->end_time}",
                        $attendance->id
                    );
                    
                    // Create notification record
                    AbsenceNotification::create([
                        'attendance_record_id' => $attendance->id,
                        'student_id' => $attendance->student_id,
                        'session_id' => $attendance->session_id,
                        'notification_type' => 'telegram',
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'telegram_message_id' => $result['message_id'] ?? null,
                        'error_message' => $result['error'] ?? null,
                        'sent_at' => $result['success'] ? now() : null,
                    ]);
                    
                    if (!$result['success']) {
                        Log::warning('Telegram notification failed directly', [
                            'attendance_id' => $attendance->id,
                            'error' => $result['error'] ?? 'Unknown error',
                        ]);
                    }
                }
            } else {
                // Dispatch to background queue for better performance
                SendTelegramNotificationJob::dispatch(
                    $attendance->student_id,
                    $attendance->session_id,
                    $attendance->id
                )->onQueue('telegram_notifications');
            }

            Log::debug('Absence notification processed', [
                'student_id' => $attendance->student_id,
                'attendance_id' => $attendance->id,
                'queue' => $queueConnection,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unexpected error while sending absence notification', [
                'attendance_id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
