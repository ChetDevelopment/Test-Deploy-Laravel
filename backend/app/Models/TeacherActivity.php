<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TeacherActivity extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'session_id',
        'action',
        'ip_address'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
