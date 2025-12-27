<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Attendance extends Model
// {
//     protected $fillable = [
//         'student_id',
//         'attendance_date',
//         'check_in',
//         'check_out',
//         'image_path',
//         'confidence_score'
//     ];

//     protected $casts = [
//         'attendance_date' => 'date',
//     ];

//     public function student()
//     {
//         return $this->belongsTo(Student::class);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    /**
     * ========== PERUBAHAN BESAR: GABUNG DENGAN DETECTION DATA ==========
     * File ini menggabungkan data absensi + deteksi mabuk
     * Menggantikan tabel 'detections' yang dihapus
     * 
     * FIELD BARU:
     * - attendance_image: Gambar_Absen dari Face Recognition
     * - drunk_confidence_score: Confidence score dari model AI
     * - drunk_status: Status deteksi (sober/suspected/drunk)
     * - red_eyes, unstable_posture: Detail deteksi
     * - parent_notified: Tracking notifikasi ke orang tua
     * ===================================================================
     */
    protected $fillable = [
        'student_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
        
        // Face Recognition & Drunk Detection
        'attendance_image',
        'drunk_confidence_score',
        'drunk_status',
        'red_eyes',
        'unstable_posture',
        'ai_notes',
        
        // Notification tracking
        'parent_notified',
        'notified_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'drunk_confidence_score' => 'decimal:2',
        'red_eyes' => 'boolean',
        'unstable_posture' => 'boolean',
        'parent_notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'drunk_status_label',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function whatsappNotifications(): HasMany
    {
        return $this->hasMany(WhatsappNotification::class);
    }

    // ========== ACCESSORS ==========
    
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'excused' => 'Izin',
            default => 'Unknown',
        };
    }

    public function getDrunkStatusLabelAttribute(): string
    {
        return match($this->drunk_status) {
            'sober' => 'Normal',
            'suspected' => 'Terindikasi',
            'drunk' => 'Mabuk',
            default => 'Unknown',
        };
    }

    // ========== HELPER METHODS - ATTENDANCE ==========
    
    public function isPresent(): bool
    {
        return $this->status === 'present';
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isExcused(): bool
    {
        return $this->status === 'excused';
    }

    public function hasCheckedIn(): bool
    {
        return !is_null($this->check_in_time);
    }

    public function hasCheckedOut(): bool
    {
        return !is_null($this->check_out_time);
    }

    public function getAttendanceDuration(): ?int
    {
        if (!$this->hasCheckedIn() || !$this->hasCheckedOut()) {
            return null;
        }

        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }

    // ========== HELPER METHODS - DRUNK DETECTION ==========
    
    public function isDrunk(): bool
    {
        return $this->drunk_status === 'drunk';
    }

    public function isSuspected(): bool
    {
        return $this->drunk_status === 'suspected';
    }

    public function isSober(): bool
    {
        return $this->drunk_status === 'sober';
    }

    public function needsAttention(): bool
    {
        return in_array($this->drunk_status, ['suspected', 'drunk']);
    }

    public function needsNotification(): bool
    {
        return $this->needsAttention() && !$this->parent_notified;
    }

    public function hasSymptoms(): bool
    {
        return $this->red_eyes || $this->unstable_posture;
    }

    public function getConfidenceLevel(): string
    {
        if (!$this->drunk_confidence_score) {
            return 'unknown';
        }

        if ($this->drunk_confidence_score >= 80) {
            return 'very_high';
        } elseif ($this->drunk_confidence_score >= 60) {
            return 'high';
        } elseif ($this->drunk_confidence_score >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    public function getDetectionSummary(): string
    {
        $parts = [];
        
        if ($this->red_eyes) {
            $parts[] = 'Mata Merah';
        }
        
        if ($this->unstable_posture) {
            $parts[] = 'Postur Tidak Stabil';
        }
        
        if ($this->drunk_confidence_score) {
            $parts[] = sprintf('Confidence: %.1f%%', $this->drunk_confidence_score);
        }

        return empty($parts) ? 'Tidak ada indikasi' : implode(', ', $parts);
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'excused' => 'info',
            default => 'secondary',
        };
    }

    public function getDrunkStatusBadgeColor(): string
    {
        return match($this->drunk_status) {
            'drunk' => 'danger',
            'suspected' => 'warning',
            'sober' => 'success',
            default => 'secondary',
        };
    }

    public function markParentNotified(): void
    {
        $this->update([
            'parent_notified' => true,
            'notified_at' => now(),
        ]);
    }

    // ========== SCOPES - ATTENDANCE ==========
    
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    // ========== SCOPES - DRUNK DETECTION ==========
    
    public function scopeDrunk($query)
    {
        return $query->where('drunk_status', 'drunk');
    }

    public function scopeSuspected($query)
    {
        return $query->where('drunk_status', 'suspected');
    }

    public function scopeSober($query)
    {
        return $query->where('drunk_status', 'sober');
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('drunk_status', ['suspected', 'drunk']);
    }

    public function scopeWithSymptoms($query)
    {
        return $query->where(function($q) {
            $q->where('red_eyes', true)
              ->orWhere('unstable_posture', true);
        });
    }

    public function scopeHighConfidence($query, $threshold = 70)
    {
        return $query->where('drunk_confidence_score', '>=', $threshold);
    }

    public function scopeNotificationNotSent($query)
    {
        return $query->where('parent_notified', false);
    }

    // ========== SCOPES - DATE ==========
    
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}