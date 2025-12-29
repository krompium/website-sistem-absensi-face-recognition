<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class SendWeeklyReports extends Command
{
    /**
     * Nama command untuk dijalankan di terminal/scheduler
     */
    protected $signature = 'attendance:send-weekly-reports';

    /**
     * Deskripsi command
     */
    protected $description = 'Mengirim rekap absensi mingguan (Senin-Jumat) ke orang tua via WhatsApp';

    /**
     * Eksekusi command
     */
    public function handle(WhatsAppService $waService)
    {
        $this->info('Memulai pengiriman laporan mingguan...');

        // Set periode: Senin lalu sampai Jumat hari ini
        // Asumsi command dijalankan hari Jumat sore
        $endDate = Carbon::now(); 
        $startDate = Carbon::now()->startOfWeek(); // Senin

        $this->info("Periode: " . $startDate->toDateString() . " s/d " . $endDate->toDateString());

        // Ambil semua siswa yang punya nomor wali
        $students = Siswa::whereNotNull('nomor_wali')
                         ->where('nomor_wali', '!=', '')
                         ->get();

        $bar = $this->output->createProgressBar(count($students));
        $bar->start();

        foreach ($students as $student) {
            try {
                // Ambil data absensi range Senin-Jumat untuk siswa ini
                $attendances = Absensi::where('kode_siswa', $student->kode_siswa)
                    ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('tanggal', 'asc')
                    ->get();

                // Hitung ringkasan
                $summary = [
                    'hadir' => $attendances->where('status', 'HADIR')->count(),
                    'sakit' => $attendances->where('status', 'SAKIT')->count(),
                    'izin'  => $attendances->where('status', 'IZIN')->count(),
                    'alpa'  => $attendances->where('status', 'ALPA')->count(),
                ];

                // Siapkan detail harian (Senin - Jumat)
                $details = [];
                $period = CarbonPeriod::create($startDate, $endDate);
                
                foreach ($period as $date) {
                    // Skip jika Sabtu/Minggu (safety check)
                    if ($date->isWeekend()) continue;

                    $dataHariIni = $attendances->firstWhere('tanggal', $date);
                    
                    if ($dataHariIni) {
                        $details[] = [
                            'hari' => $date->locale('id')->dayName, // Senin, Selasa, dst
                            'status' => $dataHariIni->status,
                            'status_label' => $dataHariIni->status_label, 
                            'jam_masuk' => $dataHariIni->jam_masuk
                        ];
                    } else {
                        // Jika tidak ada record, anggap Alpha
                        $details[] = [
                            'hari' => $date->locale('id')->dayName,
                            'status' => 'ALPA', 
                            'status_label' => 'Tanpa Keterangan',
                            'jam_masuk' => null
                        ];
                        // Increment Alpa count di summary jika belum terhitung
                        $summary['alpa']++;
                    }
                }

                // Kirim Pesan
                $waService->sendWeeklyReport($student, $startDate, $endDate, $summary, $details);
                
                // Jeda agar tidak dianggap spam (rate limit)
                $delay = rand(5, 15);
                sleep($delay);

            } catch (\Exception $e) {
                Log::error("Gagal kirim laporan mingguan ke {$student->nama_siswa}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai mengirim laporan mingguan.');
    }
}