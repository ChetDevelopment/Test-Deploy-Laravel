<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'status',
        'location',
        'submitted_by',
        'date',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public static function alreadySubmitted($student_id, $session_id): bool
    {
        return self::where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->exists();
    }

    /**
     * Get the student associated with this attendance record.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the session associated with this attendance record.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the user who submitted this attendance record.
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
