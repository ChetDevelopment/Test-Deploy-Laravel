<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceNotification extends Model
{
    // Status constants for absence (excused/unexcused)
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_EXCUSED = 'EXCUSED';
    public const STATUS_UNEXCUSED = 'UNEXCUSED';

    // Notification status constants
    public const NOTIFICATION_PENDING = 'pending';
    public const NOTIFICATION_SENT = 'sent';
    public const NOTIFICATION_FAILED = 'failed';

    protected $fillable = [
        'attendance_record_id',
        'student_id',
        'session_id',
        'notification_type',
        'status',
        'telegram_message_id',
        'error_message',
        'sent_at',
        'absence_reason',
        'reason_submitted_by',
        'reason_submitted_at',
        'absence_status',
        'comment',
        'follow_up_notes',
        'status_updated_at',
        'status_updated_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'reason_submitted_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    /**
     * Get the attendance record associated with this notification.
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Get the student associated with this notification.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the session associated with this notification.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the user who updated the status.
     */
    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    /**
     * Get the user who submitted the reason.
     */
    public function reasonSubmittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reason_submitted_by');
    }

    /**
     * Check if absence is pending.
     */
    public function isPending(): bool
    {
        return $this->absence_status === self::STATUS_PENDING;
    }

    /**
     * Check if absence is excused.
     */
    public function isExcused(): bool
    {
        return $this->absence_status === self::STATUS_EXCUSED;
    }

    /**
     * Check if absence is unexcused.
     */
    public function isUnexcused(): bool
    {
        return $this->absence_status === self::STATUS_UNEXCUSED;
    }

    /**
     * Get all available absence statuses.
     */
    public static function getAbsenceStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_EXCUSED => 'Excused',
            self::STATUS_UNEXCUSED => 'Unexcused',
        ];
    }

    /**
     * Scope to filter by absence status.
     */
    public function scopeByAbsenceStatus($query, string $status)
    {
        return $query->where('absence_status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
