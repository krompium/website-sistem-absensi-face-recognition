<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\Absensi;
// use App\Models\Notification; // <-- HAPUS INI
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDailyReports extends Command
{
    protected $signature = 'attendance:send-daily-reports';
    protected $description = 'Kirim laporan harian (Mode Job Queue - Tanpa Tabel Log)';

    public function handle()
    {
        $this->info('Memulai pengiriman laporan harian...');

        $today = Carbon::today();
        
        $attendances = Absensi::with(['siswa'])
            ->whereDate('tanggal', $today)
            ->get();

        $sentCount = 0;

        foreach ($attendances as $attendance) {
            $student = $attendance->siswa;

            if (!$student || empty($student->nomor_wali)) {
                continue;
            }

            // Generate pesan
            $messageContent = $this->buildDailyReportMessage($student, $attendance);

            // ==================================================
            // PERUBAHAN DISINI:
            // Langsung Dispatch Job dengan (Nomor HP, Pesan)
            // Tidak perlu create data di tabel Notification dulu
            // ==================================================
            SendWhatsAppNotificationJob::dispatch(
                $student->nomor_wali, 
                $messageContent
            );

            $sentCount++;
        }

        $this->info("Antrian laporan berhasil dibuat: {$sentCount}");

        return Command::SUCCESS;
    }

    protected function buildDailyReportMessage($student, $attendance): string
    {
        // ... (Kode build message sama persis seperti sebelumnya) ...
        // Copy paste fungsi buildDailyReportMessage dari jawaban sebelumnya
        return app(\App\Services\WhatsAppService::class)
               ->buildDailyReportMessage($student, $attendance);
    }
}