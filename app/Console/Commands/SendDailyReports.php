<?php
// app/Console/Commands/SendDailyReports.php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Notification;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDailyReports extends Command
{
    protected $signature = 'attendance:send-daily-reports';
    protected $description = 'Send daily attendance reports to all parents';

    public function handle()
    {
        $this->info('Sending daily attendance reports...');

        $today = Carbon::today();
        $attendances = Attendance::with(['student'])
            ->whereDate('date', $today)
            ->get();

        $sentCount = 0;

        foreach ($attendances as $attendance) {
            $student = $attendance->student;

            // Create notification
            $notification = Notification::create([
                'student_id' => $student->id,
                'type' => 'attendance',
                'recipient_phone' => $student->parent_phone,
                'recipient_name' => $student->parent_name,
                'title' => 'Laporan Kehadiran Harian',
                'message' => $this->buildDailyReportMessage($student, $attendance),
                'status' => 'pending',
            ]);

            // Dispatch job
            SendWhatsAppNotificationJob::dispatch($notification);

            $sentCount++;
        }

        $this->info("Daily reports queued: {$sentCount}");

        return Command::SUCCESS;
    }

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
}
