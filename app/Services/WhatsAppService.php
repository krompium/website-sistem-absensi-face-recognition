<?php

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
        
        // Konfigurasi Fonnte
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    /**
     * Send WhatsApp message
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
        return $this->sendMessage($student->nomor_wali, $message); 
    }

    /**
     * Send late arrival notification
     */
    public function sendLateNotification($student, $attendance): array
    {
        $message = $this->buildLateMessage($student, $attendance);
        return $this->sendMessage($student->nomor_wali, $message);
    }

    /**
     * Send absent notification
     */
    public function sendAbsentNotification($student, $date): array
    {
        $message = $this->buildAbsentMessage($student, $date);
        return $this->sendMessage($student->nomor_wali, $message);
    }

    /**
     * Send daily attendance report
     */
    public function sendDailyReport($student, $attendance): array
    {
        $message = $this->buildDailyReportMessage($student, $attendance);
        return $this->sendMessage($student->nomor_wali, $message);
    }

    /**
     * BARU: Kirim Laporan Mingguan
     */
    public function sendWeeklyReport($student, $startDate, $endDate, $summary, $details): array
    {
        $message = $this->buildWeeklyReportMessage($student, $startDate, $endDate, $summary, $details);
        
        return $this->sendMessage(
            $student->nomor_wali,
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
        $message .= "Kepada Yth. Wali Murid,\n\n";
        $message .= "Kami menginformasikan bahwa putra/putri Anda:\n\n";
        $message .= "📝 Nama: *{$student->nama_siswa}*\n";
        $message .= "🎓 Kode: *{$student->kode_siswa}*\n";
        // Cek relasi kelas jika ada
        $kelas = $student->kelas ? $student->kelas->nama_kelas : '-';
        $message .= "🏫 Kelas: *{$kelas}*\n\n";
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

        return $message;
    }

    /**
     * Build late arrival message
     */
    protected function buildLateMessage($student, $attendance): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $date = $attendance->tanggal->format('d/m/Y');
        $time = $attendance->jam_masuk->format('H:i');

        $message = "*INFORMASI KETERLAMBATAN*\n\n";
        $message .= "Kepada Yth. Wali Murid,\n\n";
        $message .= "Kami informasikan bahwa:\n\n";
        $message .= "📝 Nama: *{$student->nama_siswa}*\n";
        $message .= "🎓 Kode: *{$student->kode_siswa}*\n";
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
        $message .= "Kepada Yth. Wali Murid,\n\n";
        $message .= "Kami informasikan bahwa:\n\n";
        $message .= "📝 Nama: *{$student->nama_siswa}*\n";
        $message .= "🎓 Kode: *{$student->kode_siswa}*\n";
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
    public function buildDailyReportMessage($student, $attendance): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $date = $attendance->tanggal->format('d/m/Y');
        
        $checkIn = $attendance->jam_masuk ? $attendance->jam_masuk->format('H:i') : '-';
        $checkOut = $attendance->jam_keluar ? $attendance->jam_keluar->format('H:i') : '-';

        $status = match($attendance->status) {
            'HADIR' => '✅ Hadir',
            'IZIN' => '📝 Izin',
            'SAKIT' => '🤒 Sakit',
            'ALPA' => '❌ Tidak Hadir',
            default => 'Unknown',
        };

        $message = "*LAPORAN KEHADIRAN HARIAN*\n\n";
        $message .= "Kepada Yth. Wali Murid,\n\n";
        $message .= "📝 Nama: *{$student->nama_siswa}*\n";
        $message .= "🎓 Kode: *{$student->kode_siswa}*\n";
        $message .= "📅 Tanggal: {$date}\n";
        $message .= "Status: {$status}\n";
        $message .= "🕐 Jam Masuk: {$checkIn}\n";
        $message .= "🕐 Jam Pulang: {$checkOut}\n";

        $message .= "\nTerima kasih,\n";
        $message .= "*{$schoolName}*";

        return $message;
    }

    /**
     * BARU: Build Weekly Report Message
     */
    public function buildWeeklyReportMessage($student, $startDate, $endDate, $summary, $details): string
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $startStr = $startDate->translatedFormat('d F');
        $endStr = $endDate->translatedFormat('d F Y');

        $message = "*LAPORAN ABSENSI MINGGUAN*\n";
        $message .= "🗓 Periode: {$startStr} s.d {$endStr}\n\n";
        $message .= "Kepada Yth. Wali Murid,\n";
        $message .= "Berikut rekap kehadiran siswa:\n\n";
        $message .= "📝 Nama: *{$student->nama_siswa}*\n";
        $message .= "🎓 Kode: *{$student->kode_siswa}*\n";
        if ($student->kelas) {
             $message .= "🏫 Kelas: *{$student->kelas->nama_kelas}*\n\n";
        } else {
             $message .= "\n";
        }

        $message .= "*Ringkasan:*\n";
        $message .= "✅ Hadir: {$summary['hadir']}\n";
        $message .= "🤒 Sakit: {$summary['sakit']}\n";
        $message .= "📝 Izin: {$summary['izin']}\n";
        $message .= "❌ Alpha: {$summary['alpa']}\n\n";

        $message .= "*Rincian Harian:*\n";
        foreach ($details as $detail) {
            $icon = match($detail['status']) {
                'HADIR' => '✅',
                'SAKIT' => '🤒',
                'IZIN'  => '📝',
                'ALPA'  => '❌',
                default => '❓',
            };
            
            $jam = $detail['jam_masuk'] ? $detail['jam_masuk']->format('H:i') : '-';
            $message .= "• {$detail['hari']}: {$icon} {$detail['status_label']} ({$jam})\n";
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
            // Ganti endpoint '/send' menjadi '/device'
            $url = str_replace('/send', '/device', $this->apiUrl);

            // Gunakan method POST (bukan GET) sesuai dokumentasi Fonnte
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            return [
                'success' => true,
                // Ambil status dari 'device_status' (connect/disconnect)
                'status' => $result['device_status'] ?? 'unknown',
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