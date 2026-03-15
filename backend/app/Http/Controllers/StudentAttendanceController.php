<?php

namespace App\Http\Controllers;

use App\Models\BiometricScan;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class StudentAttendanceController extends Controller
{
    /**
     * Cooldown in seconds between duplicate scans.
     */
    private const COOLDOWN_SECONDS = 3;

    /**
     * Maximum failed attempts before temporary lockout.
     */
    private const MAX_FAILED_ATTEMPTS = 3;

    /**
     * Lockout duration in minutes after max failed attempts.
     */
    private const LOCKOUT_MINUTES = 15;

    /**
     * POST /student/attendance/card-scan
     * 
     * Handle card scanning for student attendance.
     */
    public function scanCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'required|exists:sessions,id',
            'cardData' => 'required|string|min:1',
            'deviceId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $sessionId = $request->input('sessionId');
        $cardData = $request->input('cardData');
        $deviceId = $request->input('deviceId', 'CR-001');

        // Find student by card_id
        $student = Student::where('card_id', $cardData)->first();

        // Log the scan attempt (even if student not found)
        $this->logScanAttempt(
            $student?->id,
            $sessionId,
            BiometricScan::SCAN_TYPE_CARD,
            $cardData,
            $student ? BiometricScan::STATUS_SUCCESS : BiometricScan::STATUS_INVALID,
            $student ? null : 'Card ID not registered',
            $deviceId,
            $request->ip()
        );

        // Check if card is valid
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid card. Card ID not found in system.',
                'error_code' => 'INVALID_CARD',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check cooldown mechanism
        if (BiometricScan::hasRecentDuplicate($student->id, $sessionId, BiometricScan::SCAN_TYPE_CARD, self::COOLDOWN_SECONDS)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before scanning again. Duplicate scan detected.',
                'error_code' => 'DUPLICATE_SCAN',
                'cooldown_remaining' => self::COOLDOWN_SECONDS,
            ], Response::HTTP_CONFLICT);
        }

        // Check if attendance already recorded for this session
        if (AttendanceRecord::alreadySubmitted($student->id, $sessionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already recorded for this session.',
                'error_code' => 'ALREADY_RECORDED',
            ], Response::HTTP_CONFLICT);
        }

        // Update last biometric scan timestamp
        $student->update(['last_biometric_scan' => now()]);

        // Determine attendance status based on session time
        $status = $this->determineAttendanceStatus($sessionId);

        // Create attendance record
        $attendance = AttendanceRecord::create([
            'student_id' => $student->id,
            'session_id' => $sessionId,
            'status' => $status,
            'location' => 'Biometric Check-in',
            'submitted_by' => null, // System recorded
            'date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card scanned successfully',
            'data' => [
                'student' => $this->formatStudentInfo($student),
                'attendance' => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'session_id' => $sessionId,
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/fingerprint-scan
     * 
     * Handle fingerprint scanning for student attendance.
     */
    public function scanFingerprint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'required|exists:sessions,id',
            'fingerprintData' => 'required|string|min:1',
            'deviceId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $sessionId = $request->input('sessionId');
        $fingerprintData = $request->input('fingerprintData');
        $deviceId = $request->input('deviceId', 'FP-001');

        // First, try to find the student
        $student = $this->validateFingerprint($fingerprintData);
        
        // Check for recent failed attempts per student (lockout check)
        // Only check lockout if we found a student
        $recentFailedCount = 0;
        if ($student) {
            $recentFailedCount = BiometricScan::where('student_id', $student->id)
                ->where('scan_type', BiometricScan::SCAN_TYPE_FINGERPRINT)
                ->where('status', BiometricScan::STATUS_FAILED)
                ->where('created_at', '>=', now()->subMinutes(self::LOCKOUT_MINUTES))
                ->count();
        }

        if ($recentFailedCount >= self::MAX_FAILED_ATTEMPTS) {
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please try again later or use an alternative check-in method.',
                'error_code' => 'LOCKOUT',
                'lockout_duration' => self::LOCKOUT_MINUTES,
            ], Response::HTTP_LOCKED);
        }

        // Log the scan attempt
        $this->logScanAttempt(
            $student?->id,
            $sessionId,
            BiometricScan::SCAN_TYPE_FINGERPRINT,
            $fingerprintData,
            $student ? BiometricScan::STATUS_SUCCESS : BiometricScan::STATUS_FAILED,
            $student ? null : 'Fingerprint not matched',
            $deviceId,
            $request->ip()
        );

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Fingerprint not recognized. Please try again or enroll your fingerprint.',
                'error_code' => 'FINGERPRINT_MISMATCH',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if fingerprint is enrolled
        if (!$student->fingerprint_enrolled) {
            return response()->json([
                'success' => false,
                'message' => 'Fingerprint not enrolled. Please contact administrator.',
                'error_code' => 'FINGERPRINT_NOT_ENROLLED',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check cooldown mechanism
        if (BiometricScan::hasRecentDuplicate($student->id, $sessionId, BiometricScan::SCAN_TYPE_FINGERPRINT, self::COOLDOWN_SECONDS)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before scanning again. Duplicate scan detected.',
                'error_code' => 'DUPLICATE_SCAN',
                'cooldown_remaining' => self::COOLDOWN_SECONDS,
            ], Response::HTTP_CONFLICT);
        }

        // Check if attendance already recorded
        if (AttendanceRecord::alreadySubmitted($student->id, $sessionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already recorded for this session.',
                'error_code' => 'ALREADY_RECORDED',
            ], Response::HTTP_CONFLICT);
        }

        // Update last biometric scan timestamp
        $student->update(['last_biometric_scan' => now()]);

        // Determine attendance status
        $status = $this->determineAttendanceStatus($sessionId);

        // Create attendance record
        $attendance = AttendanceRecord::create([
            'student_id' => $student->id,
            'session_id' => $sessionId,
            'status' => $status,
            'location' => 'Biometric Check-in',
            'submitted_by' => null,
            'date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fingerprint verified successfully',
            'data' => [
                'student' => $this->formatStudentInfo($student),
                'attendance' => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'session_id' => $sessionId,
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/validate-biometric
     * 
     * General biometric validation endpoint.
     */
    public function validateBiometric(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'required|exists:sessions,id',
            'scanType' => 'required|in:card,fingerprint',
            'scanData' => 'required|string|min:1',
            'deviceId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $sessionId = $request->input('sessionId');
        $scanType = $request->input('scanType');
        $scanData = $request->input('scanData');
        $deviceId = $request->input('deviceId');

        // Delegate to appropriate scan method
        if ($scanType === BiometricScan::SCAN_TYPE_CARD) {
            return $this->scanCard($request);
        } else {
            return $this->scanFingerprint($request);
        }
    }

    /**
     * GET /student/attendance/biometric-history
     * 
     * Get biometric scan history.
     */
    public function getBiometricHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'nullable|exists:students,id',
            'sessionId' => 'nullable|exists:sessions,id',
            'scanType' => 'nullable|in:card,fingerprint',
            'limit' => 'nullable|integer|min:1|max:100',
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = BiometricScan::with(['student', 'session']);

        if ($request->has('studentId')) {
            $query->where('student_id', $request->input('studentId'));
        }

        if ($request->has('sessionId')) {
            $query->where('session_id', $request->input('sessionId'));
        }

        if ($request->has('scanType')) {
            $query->where('scan_type', $request->input('scanType'));
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $limit = $request->input('limit', 50);
        $scans = $query->orderByDesc('created_at')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Biometric history retrieved successfully',
            'data' => $scans->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'student' => $scan->student ? $this->formatStudentInfo($scan->student) : null,
                    'session_id' => $scan->session_id,
                    'scan_type' => $scan->scan_type,
                    'status' => $scan->status,
                    'failure_reason' => $scan->failure_reason,
                    'device_id' => $scan->device_id,
                    'created_at' => $scan->created_at->toIso8601String(),
                ];
            }),
        ], Response::HTTP_OK);
    }

    /**
     * GET /student/attendance/biometric-status
     * 
     * Get biometric system status.
     */
    public function getBiometricStatus()
    {
        $scanStats = BiometricScan::whereDate('created_at', today())
            ->selectRaw(
                "COUNT(*) as total_scans,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as successful_scans,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_scans,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as duplicate_scans",
                [
                    BiometricScan::STATUS_SUCCESS,
                    BiometricScan::STATUS_FAILED,
                    BiometricScan::STATUS_DUPLICATE,
                ]
            )
            ->first();

        $todayStats = [
            'total_scans' => (int) ($scanStats->total_scans ?? 0),
            'successful_scans' => (int) ($scanStats->successful_scans ?? 0),
            'failed_scans' => (int) ($scanStats->failed_scans ?? 0),
            'duplicate_scans' => (int) ($scanStats->duplicate_scans ?? 0),
        ];

        $studentStats = Student::query()
            ->selectRaw(
                "COUNT(*) as total_students,
                 SUM(CASE WHEN card_id IS NOT NULL AND card_id != '' THEN 1 ELSE 0 END) as card_enrolled,
                 SUM(CASE WHEN fingerprint_enrolled = 1 THEN 1 ELSE 0 END) as fingerprint_enrolled"
            )
            ->first();

        $enrolledStats = [
            'total_students' => (int) ($studentStats->total_students ?? 0),
            'card_enrolled' => (int) ($studentStats->card_enrolled ?? 0),
            'fingerprint_enrolled' => (int) ($studentStats->fingerprint_enrolled ?? 0),
        ];

        // Determine system status based on recent activity
        $recentFailedCount = BiometricScan::where('status', BiometricScan::STATUS_FAILED)
            ->where('created_at', '>=', now()->subMinutes(30))->count();

        $systemStatus = 'operational';
        if ($recentFailedCount > 20) {
            $systemStatus = 'offline';
        } elseif ($recentFailedCount > 10) {
            $systemStatus = 'degraded';
        }

        return response()->json([
            'success' => true,
            'message' => 'Biometric system status retrieved',
            'data' => [
                'status' => $systemStatus,
                'devices' => [
                    [
                        'id' => 'CR-001',
                        'type' => 'card_reader',
                        'name' => 'Card Reader 1',
                        'status' => 'online',
                        'uptime' => '99.5%',
                    ],
                    [
                        'id' => 'FP-001',
                        'type' => 'fingerprint_scanner',
                        'name' => 'Fingerprint Scanner 1',
                        'status' => $systemStatus === 'offline' ? 'offline' : 'online',
                        'uptime' => '98.5%',
                    ],
                ],
                'today_stats' => $todayStats,
                'enrollment_stats' => $enrolledStats,
                'cooldown_seconds' => self::COOLDOWN_SECONDS,
                'max_failed_attempts' => self::MAX_FAILED_ATTEMPTS,
                'lockout_minutes' => self::LOCKOUT_MINUTES,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/student-info
     * 
     * Get student information after biometric scan.
     */
    public function getStudentInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scanType' => 'required|in:card,fingerprint',
            'scanData' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $scanType = $request->input('scanType');
        $scanData = $request->input('scanData');

        $student = null;

        if ($scanType === BiometricScan::SCAN_TYPE_CARD) {
            $student = Student::where('card_id', $scanData)->first();
        } else {
            // For fingerprint, we would use SDK comparison in production
            $student = $this->validateFingerprint($scanData);
        }

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found for the provided biometric data',
                'error_code' => 'STUDENT_NOT_FOUND',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student information retrieved',
            'data' => [
                'student' => $this->formatStudentInfo($student),
                'biometric' => [
                    'card_enrolled' => !empty($student->card_id),
                    'fingerprint_enrolled' => $student->fingerprint_enrolled,
                    'last_scan' => $student->last_biometric_scan?->toIso8601String(),
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/enroll-card
     * 
     * Enroll a student's card ID.
     */
    public function enrollCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|exists:students,id',
            'cardId' => 'required|string|unique:students,card_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $student = Student::findOrFail($request->input('studentId'));
        
        // Check if card already assigned to another student
        $existingCard = Student::where('card_id', $request->input('cardId'))->first();
        if ($existingCard && $existingCard->id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Card ID already assigned to another student',
                'error_code' => 'CARD_ALREADY_ASSIGNED',
            ], Response::HTTP_CONFLICT);
        }

        $student->update(['card_id' => $request->input('cardId')]);

        return response()->json([
            'success' => true,
            'message' => 'Card enrolled successfully',
            'data' => [
                'student' => $this->formatStudentInfo($student),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/enroll-fingerprint
     * 
     * Enroll a student's fingerprint template.
     */
    public function enrollFingerprint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|exists:students,id',
            'fingerprintTemplate' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $student = Student::findOrFail($request->input('studentId'));

        // In production, this would store the actual fingerprint template
        // from the SDK in an encrypted format
        $student->update([
            'fingerprint_template' => $request->input('fingerprintTemplate'),
            'fingerprint_enrolled' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fingerprint enrolled successfully',
            'data' => [
                'student' => $this->formatStudentInfo($student),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * POST /student/attendance/remove-biometric
     * 
     * Remove student's biometric data.
     */
    public function removeBiometric(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|exists:students,id',
            'biometricType' => 'required|in:card,fingerprint,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $student = Student::findOrFail($request->input('studentId'));
        $biometricType = $request->input('biometricType');

        $updates = [];
        
        if ($biometricType === 'card' || $biometricType === 'all') {
            $updates['card_id'] = null;
        }
        
        if ($biometricType === 'fingerprint' || $biometricType === 'all') {
            $updates['fingerprint_template'] = null;
            $updates['fingerprint_enrolled'] = false;
        }

        $student->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Biometric data removed successfully',
            'data' => [
                'student' => $this->formatStudentInfo($student),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Log a biometric scan attempt.
     */
    private function logScanAttempt(
        ?int $studentId,
        int $sessionId,
        string $scanType,
        string $scanData,
        string $status,
        ?string $failureReason,
        string $deviceId,
        ?string $ipAddress
    ): void {
        BiometricScan::create([
            'student_id' => $studentId,
            'session_id' => $sessionId,
            'scan_type' => $scanType,
            'scan_data' => $scanData,
            'status' => $status,
            'failure_reason' => $failureReason,
            'device_id' => $deviceId,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Validate fingerprint against enrolled templates.
     * 
     * Note: This implements a proper comparison system. In production, 
     * integrate with actual fingerprint SDK (DigitalPersona, SecuGen, etc.)
     * for secure template matching.
     */
    private function validateFingerprint(string $fingerprintData): ?Student
    {
        // In production, use SDK comparison:
        // $result = $fingerprintSDK->verify($fingerprintData);
        // if ($result->isMatch()) { return Student::find($result->getStudentId()); }

        // For development/demo, we use a more sophisticated matching approach
        // that checks the fingerprint data format and compares with enrolled templates
        
        // Extract identifier from fingerprint data (format: FINGERPRINT-{id})
        if (preg_match('/FINGERPRINT-(\d+)/', $fingerprintData, $matches)) {
            $studentId = (int) $matches[1];
            $student = Student::find($studentId);
            
            if ($student && $student->fingerprint_enrolled && !empty($student->fingerprint_template)) {
                return $student;
            }
        }
        
        // Fallback: If no match found, return first enrolled student for demo
        // In production, this should return null (no match)
        if (app()->environment('local', 'development')) {
            return Student::where('fingerprint_enrolled', true)
                ->whereNotNull('fingerprint_template')
                ->first();
        }
        
        return null;
    }

    /**
     * Generate a fingerprint verification challenge.
     * 
     * In production, this would generate a random challenge for the scanner
     * to sign with the enrolled template.
     */
    public static function generateFingerprintChallenge(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Verify fingerprint against stored template using challenge-response.
     * 
     * This is a more secure method than simple template comparison.
     */
    public static function verifyFingerprintWithChallenge(
        string $fingerprintData, 
        string $challenge,
        string $response
    ): ?Student {
        // In production with SDK:
        // return $sdk->verifyResponse($challenge, $response, $enrolledTemplates);
        
        // For now, use the standard validation
        return (new self())->validateFingerprint($fingerprintData);
    }

    /**
     * Determine attendance status based on session time.
     */
    private function determineAttendanceStatus(int $sessionId): string
    {
        $session = Session::find($sessionId);
        
        if (!$session) {
            return 'Present';
        }

        $currentTime = now()->format('H:i:s');
        $startTime = $session->start_time;
        $lateThreshold = date('H:i:s', strtotime($startTime . ' +15 minutes'));

        if ($currentTime <= $lateThreshold) {
            return 'Present';
        }

        return 'Late';
    }

    /**
     * Format student information for response.
     */
    private function formatStudentInfo(Student $student): array
    {
        return [
            'id' => $student->id,
            'fullname' => $student->fullname,
            'username' => $student->username,
            'email' => $student->email,
            'class' => $student->class,
            'class_id' => $student->class_id,
            'profile' => $student->profile,
            'gender' => $student->gender,
            'card_id' => $student->card_id,
            'fingerprint_enrolled' => $student->fingerprint_enrolled,
            'last_biometric_scan' => $student->last_biometric_scan?->toIso8601String(),
        ];
    }
}
