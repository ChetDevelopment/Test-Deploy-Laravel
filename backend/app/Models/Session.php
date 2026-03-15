<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Session extends Model
{
    protected $fillable = [
        'name', 
        'start_time', 
        'end_time', 
        'order',
        'date',
        'status',
        'class_id',
        'teacher_id',
        'academic_year_id',
        'late_threshold',
        'is_active',
        'description',
    ];
    
    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'late_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    private function sessionTz(): string
    {
        return config('sessions.timezone', 'Asia/Bangkok');
    }

    private function normalizeTimeString(mixed $time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i:s');
        }

        $raw = trim((string) $time);
        if ($raw === '') {
            return '00:00:00';
        }

        if (preg_match('/\b(\d{1,2}:\d{2}(?::\d{2})?)\b/', $raw, $matches)) {
            $value = $matches[1];
            return strlen($value) === 5 ? ($value . ':00') : $value;
        }

        try {
            return Carbon::parse($raw, $this->sessionTz())->format('H:i:s');
        } catch (\Throwable) {
            return '00:00:00';
        }
    }

    private function toSessionDateTime(mixed $time, ?Carbon $referenceDate = null): Carbon
    {
        $reference = $referenceDate instanceof Carbon
            ? $referenceDate
            : Carbon::now($this->sessionTz());

        $timeString = $this->normalizeTimeString($time);

        return Carbon::parse($reference->toDateString() . ' ' . $timeString, $this->sessionTz());
    }

    /**
     * Default PNC session configurations
     * PNC runs 4 sessions per day:
     * - Session 1: 07:30 AM - 09:00 AM
     * - Session 2: 10:00 AM - 11:30 AM
     * - Session 3: 01:00 PM - 02:30 PM
     * - Session 4: 03:30 PM - 05:00 PM
     */
    public static function defaultSessions(): array
    {
        return [
            [
                'name' => 'Session 1',
                'start_time' => '07:30',
                'end_time' => '09:00',
                'order' => 1,
                'late_threshold' => 15,
                'is_active' => true,
                'description' => 'Morning Session - 07:30 AM to 09:00 AM',
            ],
            [
                'name' => 'Session 2',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'order' => 2,
                'late_threshold' => 15,
                'is_active' => true,
                'description' => 'Mid-Morning Session - 10:00 AM to 11:30 AM',
            ],
            [
                'name' => 'Session 3',
                'start_time' => '13:00',
                'end_time' => '14:30',
                'order' => 3,
                'late_threshold' => 15,
                'is_active' => true,
                'description' => 'Afternoon Session - 01:00 PM to 02:30 PM',
            ],
            [
                'name' => 'Session 4',
                'start_time' => '15:30',
                'end_time' => '17:00',
                'order' => 4,
                'late_threshold' => 15,
                'is_active' => true,
                'description' => 'Late Afternoon Session - 03:30 PM to 05:00 PM',
            ],
        ];
    }

    /**
     * Get the number of sessions per day
     */
    public static function getSessionsPerDay(): int
    {
        return config('sessions.sessions_per_day', 3);
    }

    /**
     * Get only the active sessions based on sessions_per_day setting
     */
    public static function getActiveSessionsForDay()
    {
        $sessionsPerDay = self::getSessionsPerDay();
        return self::active()
            ->ordered()
            ->take($sessionsPerDay)
            ->get();
    }

    /**
     * Check if the session is currently active based on time
     */
    public function isCurrentlyActive(): bool
    {
        $now = Carbon::now($this->sessionTz());
        return $this->isTimeWithinSession($now) && $this->is_active;
    }

    /**
     * Check if a given time is within the session window
     */
    public function isTimeWithinSession(Carbon $time): bool
    {
        $checkTime = $time instanceof Carbon ? $time : Carbon::now($this->sessionTz());

        $sessionStart = $this->toSessionDateTime((string) $this->start_time, $checkTime);
        $sessionEnd = $this->toSessionDateTime((string) $this->end_time, $checkTime);

        // If a session crosses midnight, treat the end time as next day.
        if ($sessionEnd->lessThanOrEqualTo($sessionStart)) {
            $sessionEnd = $sessionEnd->addDay();
        }

        return $checkTime->between($sessionStart, $sessionEnd);
    }

    /**
     * Check if a check-in time is late
     */
    public function isLate(Carbon $checkInTime): bool
    {
        $startTime = $this->toSessionDateTime((string) $this->start_time, $checkInTime);
        $lateThreshold = $this->late_threshold ?? 15;
        
        // Calculate the late threshold time
        $lateThresholdTime = $startTime->copy()->addMinutes($lateThreshold);
        
        return $checkInTime->greaterThan($lateThresholdTime);
    }

    /**
     * Get the late threshold time for this session
     */
    public function getLateThresholdTime(): Carbon
    {
        $startTime = $this->toSessionDateTime((string) $this->start_time);
        $lateThreshold = $this->late_threshold ?? 15;
        
        return $startTime->addMinutes($lateThreshold);
    }

    /**
     * Get the start time as a Carbon instance
     */
    public function getStartTimeCarbon(): Carbon
    {
        return $this->toSessionDateTime((string) $this->start_time);
    }

    /**
     * Get the end time as a Carbon instance
     */
    public function getEndTimeCarbon(): Carbon
    {
        return $this->toSessionDateTime((string) $this->end_time);
    }

    /**
     * Get minutes late for a given check-in time
     */
    public function getMinutesLate(Carbon $checkInTime): int
    {
        $startTime = $this->toSessionDateTime((string) $this->start_time, $checkInTime);
        
        if ($checkInTime->lessThanOrEqualTo($startTime)) {
            return 0;
        }
        
        return $checkInTime->diffInMinutes($startTime);
    }

    /**
     * Check if check-in is allowed (within session + late threshold)
     */
    public function canCheckIn(Carbon $checkInTime): bool
    {
        return $this->isTimeWithinSession($checkInTime);
    }

    /**
     * Scope to get only active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get sessions ordered by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Get the formatted time range string
     */
    public function getTimeRangeAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');
        return "{$start} - {$end}";
    }

    /**
     * Get session number from name (e.g., "Session 1" -> 1)
     */
    public function getSessionNumber(): int
    {
        preg_match('/(\d+)/', $this->name, $matches);
        return isset($matches[1]) ? (int) $matches[1] : $this->order;
    }
}
