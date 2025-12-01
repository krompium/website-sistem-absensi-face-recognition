<?php
// app/Console/Commands/CleanOldData.php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Detection;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanOldData extends Command
{
    protected $signature = 'data:cleanup {--days=365 : Number of days to keep}';
    protected $description = 'Clean up old attendance and detection data';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning data older than {$days} days (before {$cutoffDate->format('Y-m-d')})...");

        // Clean old attendances
        $attendancesCount = Attendance::where('date', '<', $cutoffDate)->count();
        
        if ($attendancesCount > 0) {
            if ($this->confirm("Delete {$attendancesCount} old attendance records?")) {
                // Delete associated photos first
                $oldAttendances = Attendance::where('date', '<', $cutoffDate)->get();
                
                foreach ($oldAttendances as $attendance) {
                    if ($attendance->check_in_photo) {
                        Storage::disk('public')->delete($attendance->check_in_photo);
                    }
                    if ($attendance->check_out_photo) {
                        Storage::disk('public')->delete($attendance->check_out_photo);
                    }
                }

                Attendance::where('date', '<', $cutoffDate)->delete();
                $this->info("Deleted {$attendancesCount} attendance records");
            }
        } else {
            $this->info("No old attendance records to delete");
        }

        // Clean old detections
        $detectionsCount = Detection::where('created_at', '<', $cutoffDate)->count();
        
        if ($detectionsCount > 0) {
            if ($this->confirm("Delete {$detectionsCount} old detection records?")) {
                Detection::where('created_at', '<', $cutoffDate)->delete();
                $this->info("Deleted {$detectionsCount} detection records");
            }
        } else {
            $this->info("No old detection records to delete");
        }

        // Clean old notifications
        $notificationsCount = Notification::where('created_at', '<', $cutoffDate)->count();
        
        if ($notificationsCount > 0) {
            if ($this->confirm("Delete {$notificationsCount} old notification records?")) {
                Notification::where('created_at', '<', $cutoffDate)->delete();
                $this->info("Deleted {$notificationsCount} notification records");
            }
        } else {
            $this->info("No old notification records to delete");
        }

        // Clean orphaned files
        $this->info("Cleaning orphaned files...");
        $this->cleanOrphanedFiles();

        $this->info("Cleanup completed!");

        return Command::SUCCESS;
    }

    protected function cleanOrphanedFiles()
    {
        $directories = ['attendance', 'faces'];
        $deletedCount = 0;

        foreach ($directories as $directory) {
            $files = Storage::disk('public')->files($directory);

            foreach ($files as $file) {
                $filename = basename($file);
                
                // Check if file is referenced in database
                $isReferenced = Attendance::where('check_in_photo', 'like', "%{$filename}%")
                    ->orWhere('check_out_photo', 'like', "%{$filename}%")
                    ->exists();

                if (!$isReferenced) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} orphaned files");
        } else {
            $this->info("No orphaned files found");
        }
    }
}
