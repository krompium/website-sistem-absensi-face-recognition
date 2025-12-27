<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\HasMany;

// class Classes extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'name',
//         'grade',
//         'major',
//         'homeroom_teacher',
//         'capacity',
//         'is_active',
//     ];

//     protected $casts = [
//         'is_active' => 'boolean',
//     ];

//     public function students(): HasMany
//     {
//         return $this->hasMany(Student::class, 'class_id');
//     }

//     public function activeStudents(): HasMany
//     {
//         return $this->hasMany(Student::class, 'class_id')->where('is_active', true);
//     }

//     // Helper method
//     public function getStudentCount(): int
//     {
//         return $this->students()->count();
//     }

//     public function isFull(): bool
//     {
//         return $this->getStudentCount() >= $this->capacity;
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Classes extends Model
{
    use HasFactory;

    /**
     * ========== PERUBAHAN: TAMBAH SEQUENCE ==========
     */
    protected $fillable = [
        'name',
        'grade',
        'major',
        'sequence', // BARU: urutan kelas (1, 2, 3)
        'homeroom_teacher',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence' => 'integer', // BARU
    ];

    // ========== RELATIONSHIPS ==========
    
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function activeStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id')->where('is_active', true);
    }

    /**
     * ========== RELASI BARU: KE TEACHER ==========
     */
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'class_id');
    }

    // ========== HELPER METHODS ==========
    
    public function getStudentCount(): int
    {
        return $this->students()->count();
    }

    public function isFull(): bool
    {
        return $this->getStudentCount() >= $this->capacity;
    }

    public function getFullName(): string
    {
        // Contoh: "XII RPL 2"
        return trim("{$this->grade} {$this->major} {$this->sequence}");
    }

    public function hasTeacher(): bool
    {
        return $this->teacher()->exists();
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeByMajor($query, $major)
    {
        return $query->where('major', $major);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('grade')
                     ->orderBy('major')
                     ->orderBy('sequence');
    }
}