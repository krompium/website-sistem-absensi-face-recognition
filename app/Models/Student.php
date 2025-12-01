<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nis',
        'name',
        'class_id',
        'gender',
        'birth_date',
        'phone',
        'parent_phone',
        'parent_name',
        'address',
        'face_image',
        'face_embeddings',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'face_embeddings' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'age',
        'gender_label',
    ];

    // Relationships
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Accessors
    public function getAgeAttribute(): int
    {
        return $this->birth_date ? $this->birth_date->age : 0;
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'male' ? 'Laki-laki' : 'Perempuan';
    }

    // Helper methods
    public function getTodayAttendance(): ?Attendance
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->first();
    }

    public function getAttendanceRate(int $days = 30): float
    {
        $total = $this->attendances()
            ->where('date', '>=', now()->subDays($days))
            ->count();
        
        $present = $this->attendances()
            ->where('date', '>=', now()->subDays($days))
            ->whereIn('status', ['present', 'late'])
            ->count();
        
        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }

    public function getAttendanceSummary(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = $this->attendances();
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'sick' => (clone $query)->where('status', 'sick')->count(),
            'permission' => (clone $query)->where('status', 'permission')->count(),
        ];
    }

    public function hasFaceData(): bool
    {
        return !empty($this->face_image) && !empty($this->face_embeddings);
    }

    public function hasCheckedInToday(): bool
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->whereNotNull('check_in_time')
            ->exists();
    }

    public function hasCheckedOutToday(): bool
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->whereNotNull('check_out_time')
            ->exists();
    }

    public function getDrunkDetectionsCount(): int
    {
        return $this->detections()
            ->whereIn('drunk_status', ['suspected', 'drunk'])
            ->count();
    }

    public function getLastDrunkDetection(): ?Detection
    {
        return $this->detections()
            ->whereIn('drunk_status', ['suspected', 'drunk'])
            ->latest()
            ->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeWithFaceData($query)
    {
        return $query->whereNotNull('face_image')
                     ->whereNotNull('face_embeddings');
    }

    public function scopeMale($query)
    {
        return $query->where('gender', 'male');
    }

    public function scopeFemale($query)
    {
        return $query->where('gender', 'female');
    }
}