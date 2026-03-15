<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'current_term',
        'status',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'academic_year_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'academic_year_id');
    }
}
