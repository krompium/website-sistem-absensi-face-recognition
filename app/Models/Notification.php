<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'detection_id',
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
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function detection(): BelongsTo
    {
        return $this->belongsTo(Detection::class);
    }

    // Helper methods
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

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'whatsapp_response' => $reason,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'sent' => 'success',
            'delivered' => 'success',
            'read' => 'info',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }
}