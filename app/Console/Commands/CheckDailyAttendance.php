<?php
// app/Console/Commands/CheckDailyAttendance.php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Notification;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckDailyAttendance extends Command
{
    protected $signature = 'attendance:check-daily';
    protected $description = 'Check daily attendance and send notifications for absent students';

    public function handle()
    {
        $this->info('Checking daily attendance...');

        $today = Carbon::today();
        $activeStudents = Student::where('is_active', true)->get();

        $absentCount = 0;
        $notificationCount = 0;

        foreach ($activeStudents as $student) {
            // Check if student has attendance record today
            $attendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance) {
                // Student is absent, create notification
                $notification = Notification::create([
                    'student_id' => $student->id,
                    'type' => 'absent',
                    'recipient_phone' => $student->parent_phone,
                    'recipient_name' => $student->parent_name,
                    'title' => 'Informasi Ketidakhadiran',
                    'message' => $this->buildAbsentMessage($student, $today),
                    'status' => 'pending',
                ]);

                // Dispatch job to send WhatsApp
                SendWhatsAppNotificationJob::dispatch($notification);

                $absentCount++;
                $notificationCount++;
            }
        }

        $this->info("Total students checked: {$activeStudents->count()}");
        $this->info("Absent students: {$absentCount}");
        $this->info("Notifications queued: {$notificationCount}");

        return Command::SUCCESS;
    }

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
}
