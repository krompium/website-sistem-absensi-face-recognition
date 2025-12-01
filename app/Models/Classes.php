<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade',
        'major',
        'homeroom_teacher',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function activeStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id')->where('is_active', true);
    }

    // Helper method
    public function getStudentCount(): int
    {
        return $this->students()->count();
    }

    public function isFull(): bool
    {
        return $this->getStudentCount() >= $this->capacity;
    }
}