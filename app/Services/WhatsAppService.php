<?php
// app/Services/WhatsAppService.php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // For development only
        ]);
        
        // You can use Fonnte, Wablas, or Twilio
        // Example using Fonnte
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    /**
     * Send WhatsApp message
     * 
     * @param string $phone - Format: 628xxxxxxxxxx
     * @param string $message
     * @param array $options - Additional options (image, file, etc)
     * @return array
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        try {
            // Validate phone format
            $phone = $this->formatPhoneNumber($phone);

            $payload = [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ];

            // Add optional parameters
            if (isset($options['image'])) {
                $payload['url'] = $options['image'];
            }

            if (isset($options['file'])) {
                $payload['filename'] = $options['file'];
            }

            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
                'form_params' => $payload,
            ]);

            $result = json_decode($response->getBody(), true);

            Log::info('WhatsApp message sent', [
                'phone' => $phone,
                'status' => $result['status'] ?? 'unknown',
                'message_id' => $result['id'] ?? null,
            ]);

            return [
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'response' => $result,
            ];

        } catch (GuzzleException $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send drunk detection notification
     */
    public function sendDrunkDetectionAlert($student, $detection): array
    {
        $message = $this->buildDrunkDetectionMessage($student, $detection);
        
        return $this->sendMessage(
            $student->parent_phone,
            $message
        );
    }

    /**
     * Send late arrival notification
     */
    public function sendLateNotification($student, $attendance): array
    {
        $message = $this->buildLateMessage($student, $attendance);
        
        return $this->sendMessage(
            $student->parent_phone,
            $message
        );
    }

    /**
     * Send absent notification
     */
    public function sendAbsentNotification($student, $date): array
    {
        $message = $this->buildAbsentMessage($student, $date);
        
        return $this->sendMessage(
            $student->parent_phone,
            $message
        );
    }

    /**
     * Send daily attendance report to parent
     */
    public function sendDailyReport($student, $attendance): array
    {
        $message = $this->buildDailyReportMessage($student, $attendance);
        
        return $this->sendMessage(
            $student->parent_phone,
            $message
        );
    }

    /**
     * Build drunk detection message
     */
    protected function buildDrunkDetectionMessage($student, $detection): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $date = now()->format('d/m/Y');
        $time = $detection->created_at->format('H:i');

        $status = match($detection->drunk_status) {
            'drunk' => '🔴 *MABUK*',
            'suspected' => '⚠️ *TERINDIKASI MABUK*',
            default => 'Normal',
        };

        $message = "*PERINGATAN PENTING*\n\n";
        $message .= "Kepada Yth. *{$student->parent_name}*,\n\n";
        $message .= "Kami menginformasikan bahwa putra/putri Anda:\n\n";
        $message .= "📝 Nama: *{$student->name}*\n";
        $message .= "🎓 NIS: *{$student->nis}*\n";
        $message .= "🏫 Kelas: *{$student->class->name}*\n\n";
        $message .= "Status: {$status}\n";
        $message .= "📅 Tanggal: {$date}\n";
        $message .= "🕐 Waktu: {$time}\n";
        $message .= "📊 Confidence: {$detection->drunk_confidence}%\n\n";

        if ($detection->red_eyes) {
            $message .= "• Mata merah terdeteksi\n";
        }
        if ($detection->unstable_posture) {
            $message .= "• Postur tidak stabil\n";
        }

        $message .= "\n⚠️ *Mohon segera menghubungi pihak sekolah untuk tindak lanjut.*\n\n";
        $message .= "Terima kasih,\n";
        $message .= "*{$schoolName}*\n";
        $message .= "📞 " . config('app.school_phone', '-');

        return $message;
    }

    /**
     * Build late arrival message
     */
    protected function buildLateMessage($student, $attendance): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $date = $attendance->date->format('d/m/Y');
        $time = $attendance->check_in_time->format('H:i');

        $message = "*INFORMASI KETERLAMBATAN*\n\n";
        $message .= "Kepada Yth. *{$student->parent_name}*,\n\n";
        $message .= "Kami informasikan bahwa:\n\n";
        $message .= "📝 Nama: *{$student->name}*\n";
        $message .= "🎓 NIS: *{$student->nis}*\n";
        $message .= "🏫 Kelas: *{$student->class->name}*\n\n";
        $message .= "⏰ *Terlambat masuk sekolah*\n";
        $message .= "📅 Tanggal: {$date}\n";
        $message .= "🕐 Jam Masuk: {$time}\n\n";
        $message .= "Mohon perhatiannya agar siswa dapat hadir tepat waktu.\n\n";
        $message .= "Terima kasih,\n";
        $message .= "*{$schoolName}*";

        return $message;
    }

    /**
     * Build absent message
     */
    protected function buildAbsentMessage($student, $date): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $dateFormatted = $date->format('d/m/Y');

        $message = "*INFORMASI KETIDAKHADIRAN*\n\n";
        $message .= "Kepada Yth. *{$student->parent_name}*,\n\n";
        $message .= "Kami informasikan bahwa:\n\n";
        $message .= "📝 Nama: *{$student->name}*\n";
        $message .= "🎓 NIS: *{$student->nis}*\n";
        $message .= "🏫 Kelas: *{$student->class->name}*\n\n";
        $message .= "❌ *Tidak hadir* pada:\n";
        $message .= "📅 Tanggal: {$dateFormatted}\n\n";
        $message .= "Jika berhalangan hadir, mohon menghubungi pihak sekolah.\n\n";
        $message .= "Terima kasih,\n";
        $message .= "*{$schoolName}*";

        return $message;
    }

    /**
     * Build daily report message
     */
    protected function buildDailyReportMessage($student, $attendance): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $date = $attendance->date->format('d/m/Y');
        
        $checkIn = $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-';
        $checkOut = $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-';

        $status = match($attendance->status) {
            'present' => '✅ Hadir',
            'late' => '⏰ Terlambat',
            'absent' => '❌ Tidak Hadir',
            'sick' => '🤒 Sakit',
            'permission' => '📝 Izin',
            default => 'Unknown',
        };

        $message = "*LAPORAN KEHADIRAN HARIAN*\n\n";
        $message .= "Kepada Yth. *{$student->parent_name}*,\n\n";
        $message .= "📝 Nama: *{$student->name}*\n";
        $message .= "🎓 NIS: *{$student->nis}*\n";
        $message .= "🏫 Kelas: *{$student->class->name}*\n\n";
        $message .= "📅 Tanggal: {$date}\n";
        $message .= "Status: {$status}\n";
        $message .= "🕐 Jam Masuk: {$checkIn}\n";
        $message .= "🕐 Jam Pulang: {$checkOut}\n";

        if ($attendance->temperature) {
            $tempIcon = $attendance->temperature >= 37.5 ? '🌡️🔴' : '🌡️';
            $message .= "{$tempIcon} Suhu: {$attendance->temperature}°C\n";
        }

        $message .= "\nTerima kasih,\n";
        $message .= "*{$schoolName}*";

        return $message;
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Check WhatsApp API status
     */
    public function checkStatus(): array
    {
        try {
            $response = $this->client->get(
                str_replace('/send', '/status', $this->apiUrl),
                [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                    ],
                ]
            );

            $result = json_decode($response->getBody(), true);

            return [
                'success' => true,
                'status' => $result['status'] ?? 'unknown',
                'data' => $result,
            ];

        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}