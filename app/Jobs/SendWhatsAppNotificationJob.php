<?php

namespace App\Jobs;

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

    // Kita ganti properti model dengan variabel biasa
    protected $phoneNumber;
    protected $message;

    public $tries = 3; // Coba 3 kali jika gagal

    /**
     * Terima nomor dan pesan langsung di constructor
     */
    public function __construct(string $phoneNumber, string $message)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            // Langsung kirim tanpa cek database
            $result = $whatsappService->sendMessage(
                $this->phoneNumber,
                $this->message
            );

            if ($result['success']) {
                // Karena tidak ada tabel, kita hanya bisa catat di Log file
                Log::info("WA Terkirim ke {$this->phoneNumber}");
            } else {
                // Jika gagal, lempar error agar Job mengulangi (retry)
                throw new \Exception($result['error'] ?? 'Gagal kirim');
            }

        } catch (\Exception $e) {
            Log::error("Gagal kirim WA ke {$this->phoneNumber}: " . $e->getMessage());
            
            // Lempar error lagi agar masuk mekanisme 'retry' Laravel
            throw $e;
        }
    }
}