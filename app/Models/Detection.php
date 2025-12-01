<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'drunk_status',
        'drunk_confidence',
        'detection_data',
        'red_eyes',
        'unstable_posture',
        'ai_analysis',
        'severity',
        'notification_sent',
        'notification_sent_at',
        'notes',
    ];

    protected $casts = [
        'drunk_confidence' => 'decimal:2',
        'detection_data' => 'array',
        'red_eyes' => 'boolean',
        'unstable_posture' => 'boolean',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];

    protected $appends = [
        'drunk_status_label',
        'severity_label',
    ];

    // Relationships
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Accessors
    public function getDrunkStatusLabelAttribute(): string
    {
        return match($this->drunk_status) {
            'sober' => 'Normal',
            'suspected' => 'Terindikasi',
            'drunk' => 'Mabuk',
            default => 'Unknown',
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            default => 'Unknown',
        };
    }

    // Helper methods
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

    public function needsNotification(): bool
    {
        return in_array($this->drunk_status, ['suspected', 'drunk']) 
            && !$this->notification_sent;
    }

    public function needsAttention(): bool
    {
        return in_array($this->drunk_status, ['suspected', 'drunk']);
    }

    public function isHighSeverity(): bool
    {
        return $this->severity === 'high';
    }

    public function isMediumSeverity(): bool
    {
        return $this->severity === 'medium';
    }

    public function isLowSeverity(): bool
    {
        return $this->severity === 'low';
    }

    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->drunk_status) {
            'drunk' => 'danger',
            'suspected' => 'warning',
            'sober' => 'success',
            default => 'secondary',
        };
    }

    public function getConfidenceLevel(): string
    {
        if (!$this->drunk_confidence) {
            return 'unknown';
        }

        if ($this->drunk_confidence >= 80) {
            return 'very_high';
        } elseif ($this->drunk_confidence >= 60) {
            return 'high';
        } elseif ($this->drunk_confidence >= 40) {
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
        
        if ($this->drunk_confidence) {
            $parts[] = sprintf('Confidence: %.1f%%', $this->drunk_confidence);
        }

        return empty($parts) ? 'Tidak ada indikasi' : implode(', ', $parts);
    }

    public function markNotificationSent(): void
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }

    public function hasSymptoms(): bool
    {
        return $this->red_eyes || $this->unstable_posture;
    }

    // Scopes
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

    public function scopeHighSeverity($query)
    {
        return $query->where('severity', 'high');
    }

    public function scopeNotificationNotSent($query)
    {
        return $query->where('notification_sent', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
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
        return $query->where('drunk_confidence', '>=', $threshold);
    }
}