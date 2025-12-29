<?php

namespace App\Observers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AbsensiObserver
{
    /**
     * Handle the Absensi "created" event.
     * Dijalankan otomatis SETIAP KALI data absensi baru masuk (Check-in)
     */
    public function created(Absensi $absensi)
    {
        $this->checkAndSendWeeklyReport($absensi);
    }

    protected function checkAndSendWeeklyReport(Absensi $absensi)
    {
        $siswa = $absensi->siswa;
        
        // 1. Validasi: Pastikan siswa ada & punya nomor wali
        if (!$siswa || empty($siswa->nomor_wali)) {
            return;
        }

        $today = Carbon::parse($absensi->tanggal);
        
        // 2. Hitung jumlah absensi minggu ini (Senin - Minggu)
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek   = $today->copy()->endOfWeek();

        $weeklyAttendances = Absensi::where('kode_siswa', $siswa->kode_siswa)
            ->whereBetween('tanggal', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('tanggal')
            ->get();

        $count = $weeklyAttendances->count();
        $isFriday = $today->isFriday();

        // 3. LOGIKA UTAMA:
        // Kirim jika Hari ini JUMAT -ATAU- Sudah ada 5 data absensi minggu ini
        if ($isFriday || $count >= 5) {
            
            // Susun Pesan Laporan Mingguan
            $message = $this->buildWeeklyMessage($siswa, $weeklyAttendances, $startOfWeek, $today);

            // Kirim via Job Queue (Langsung, tanpa tabel notification)
            SendWhatsAppNotificationJob::dispatch(
                $siswa->nomor_wali,
                $message
            );
        }
    }

    /**
     * Helper untuk menyusun teks pesan mingguan
     */
    protected function buildWeeklyMessage($student, $attendances, $start, $end)
    {
        $schoolName = config('app.school_name', 'Sekolah');
        $periode = $start->translatedFormat('d M') . " - " . $end->translatedFormat('d M Y');

        // Hitung Ringkasan
        $summary = [
            'HADIR' => 0, 'SAKIT' => 0, 'IZIN' => 0, 'ALPA' => 0, 'TERLAMBAT' => 0
        ];

        foreach ($attendances as $att) {
            // Normalisasi status ke uppercase agar match
            $statusKey = strtoupper($att->status); 
            if (isset($summary[$statusKey])) {
                $summary[$statusKey]++;
            } else {
                // Fallback jika status lain (misal: LATE masuk ke TERLAMBAT)
                $summary['HADIR']++; 
            }
        }

        // Header Pesan
        $msg = "*LAPORAN ABSENSI MINGGUAN*\n";
        $msg .= "🗓 Periode: {$periode}\n\n";
        $msg .= "Kepada Yth. Wali Murid,\n";
        $msg .= "Berikut rekap kehadiran putra/i Anda:\n\n";
        $msg .= "📝 Nama: *{$student->nama_siswa}*\n";
        $msg .= "🎓 NIS: *{$student->kode_siswa}*\n";
        if ($student->kelas) {
            $msg .= "🏫 Kelas: *{$student->kelas->nama_kelas}*\n\n";
        } else {
            $msg .= "\n";
        }

        // Body Ringkasan
        $msg .= "*Ringkasan:*\n";
        $msg .= "✅ Hadir: {$summary['HADIR']}\n";
        $msg .= "🤒 Sakit: {$summary['SAKIT']}\n";
        $msg .= "📝 Izin: {$summary['IZIN']}\n";
        // Alpa mungkin 0 karena ini trigger by presence, tapi tetap ditampilkan
        $msg .= "❌ Alpha: {$summary['ALPA']}\n\n";

        // Body Detail Harian
        $msg .= "*Rincian Minggu Ini:*\n";
        foreach ($attendances as $att) {
            $hari = Carbon::parse($att->tanggal)->locale('id')->dayName;
            $jam  = $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : '-';
            
            $icon = match(strtoupper($att->status)) {
                'HADIR' => '✅', 'SAKIT' => '🤒', 'IZIN' => '📝', 'ALPA' => '❌', default => '✅'
            };
            
            // Contoh: • Senin: ✅ Hadir (07:00)
            $msg .= "• {$hari}: {$icon} {$att->status} ({$jam})\n";
        }

        $msg .= "\nTerima kasih,\n*{$schoolName}*";

        return $msg;
    }
}