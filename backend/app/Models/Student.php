<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'username',
        'email',
        'password',
        'generation',
        'class',
        'class_id',
        'academic_year_id',
        'profile',
        'gender',
        'parent_number',
        'contact',
        'card_id',
        'fingerprint_template',
        'fingerprint_enrolled',
        'last_biometric_scan',
    ];

    protected $casts = [
        'fingerprint_enrolled' => 'boolean',
        'last_biometric_scan' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function biometricScans()
    {
        return $this->hasMany(BiometricScan::class);
    }

    /**
     * Get the user account associated with this student
     */
    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    /**
     * Automatically hash password when setting it
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Hash::make($value) : null;
    }
}
