<?php
// app/Jobs/SendWhatsAppNotificationJob.php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notification;
    public $tries = 3;
    public $backoff = [60, 120, 300]; // Retry after 1, 2, 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        // Check if WhatsApp is enabled
        if (!config('services.whatsapp.enabled')) {
            Log::info('WhatsApp notifications disabled');
            return;
        }

        // Check if already sent
        if ($this->notification->status === 'sent') {
            Log::info('Notification already sent', ['id' => $this->notification->id]);
            return;
        }

        try {
            // Send message
            $result = $whatsappService->sendMessage(
                $this->notification->recipient_phone,
                $this->notification->message
            );

            if ($result['success']) {
                // Update notification status
                $this->notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'whatsapp_message_id' => $result['message_id'] ?? null,
                    'whatsapp_response' => json_encode($result['response'] ?? []),
                ]);

                Log::info('WhatsApp notification sent successfully', [
                    'notification_id' => $this->notification->id,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Update notification
            $this->notification->update([
                'status' => 'failed',
                'whatsapp_response' => $e->getMessage(),
                'retry_count' => $this->notification->retry_count + 1,
            ]);

            // Retry if not max attempts
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp notification job failed permanently', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        $this->notification->update([
            'status' => 'failed',
            'whatsapp_response' => 'Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
        ]);
    }
}