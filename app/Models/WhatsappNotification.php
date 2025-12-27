<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class WhatsappNotification extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'student_id',
//         'detection_id',
//         'type',
//         'recipient_phone',
//         'recipient_name',
//         'title',
//         'message',
//         'status',
//         'whatsapp_response',
//         'whatsapp_message_id',
//         'sent_at',
//         'retry_count',
//     ];

//     protected $casts = [
//         'sent_at' => 'datetime',
//     ];

//     public function student(): BelongsTo
//     {
//         return $this->belongsTo(Student::class);
//     }

//     public function detection(): BelongsTo
//     {
//         return $this->belongsTo(Detection::class);
//     }

//     // Helper methods
//     public function isSent(): bool
//     {
//         return $this->status === 'sent';
//     }

//     public function isPending(): bool
//     {
//         return $this->status === 'pending';
//     }

//     public function isFailed(): bool
//     {
//         return $this->status === 'failed';
//     }

//     public function canRetry(): bool
//     {
//         return $this->isFailed() && $this->retry_count < 3;
//     }

//     public function markAsSent(string $messageId = null, string $response = null): void
//     {
//         $this->update([
//             'status' => 'sent',
//             'sent_at' => now(),
//             'whatsapp_message_id' => $messageId,
//             'whatsapp_response' => $response,
//         ]);
//     }

//     public function markAsFailed(string $reason = null): void
//     {
//         $this->update([
//             'status' => 'failed',
//             'whatsapp_response' => $reason,
//             'retry_count' => $this->retry_count + 1,
//         ]);
//     }

//     public function getStatusBadgeColor(): string
//     {
//         return match($this->status) {
//             'sent' => 'success',
//             'delivered' => 'success',
//             'read' => 'info',
//             'failed' => 'danger',
//             'pending' => 'warning',
//             default => 'secondary',
//         };
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNotification extends Model
{
    use HasFactory;

    /**
     * ========== PERUBAHAN: HAPUS DETECTION_ID ==========
     * Karena tabel detections sudah dihapus,
     * sekarang langsung relasi ke attendance_id saja
     * ==================================================
     */
    protected $fillable = [
        'student_id',
        'attendance_id', // Tetap ada, langsung ke attendances
        // 'detection_id', // DIHAPUS
        'type',
        'recipient_phone',
        'recipient_name',
        'title',
        'message',
        'status',
        'whatsapp_response',
        'whatsapp_message_id',
        'sent_at',
        'retry_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * ========== PERUBAHAN: RELASI KE ATTENDANCE ==========
     * Bukan ke detection lagi
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // ========== HELPER METHODS ==========
    
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    public function markAsSent(string $messageId = null, string $response = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_message_id' => $messageId,
            'whatsapp_response' => $response,
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'whatsapp_response' => $reason,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'sent', 'delivered' => 'success',
            'read' => 'info',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'sent' => 'Terkirim',
            'delivered' => 'Tersampaikan',
            'read' => 'Dibaca',
            'failed' => 'Gagal',
            'pending' => 'Menunggu',
            default => 'Unknown',
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'attendance' => 'Kehadiran',
            'drunk_detection' => 'Deteksi Mabuk',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'general' => 'Umum',
            default => 'Unknown',
        };
    }

    // ========== SCOPES ==========
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCanRetry($query)
    {
        return $query->where('status', 'failed')
                     ->where('retry_count', '<', 3);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
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

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}