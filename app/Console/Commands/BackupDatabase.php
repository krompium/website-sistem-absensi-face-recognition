<?php
// app/Console/Commands/BackupDatabase.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the database';

    public function handle()
    {
        $this->info('Starting database backup...');

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $date = date('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$date}.sql";
        $backupPath = storage_path("app/backups/{$filename}");

        // Create backups directory if not exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        // Execute backup
        $output = null;
        $returnVar = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $fileSize = round(filesize($backupPath) / 1024 / 1024, 2);
            $this->info("Backup completed successfully!");
            $this->info("File: {$filename}");
            $this->info("Size: {$fileSize} MB");
            $this->info("Location: {$backupPath}");

            // Clean old backups (keep last 7 days)
            $this->cleanOldBackups();

            return Command::SUCCESS;
        } else {
            $this->error("Backup failed!");
            return Command::FAILURE;
        }
    }

    protected function cleanOldBackups()
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_*.sql');
        
        // Keep only last 7 backups
        if (count($files) > 7) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            $filesToDelete = array_slice($files, 0, count($files) - 7);
            
            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->info("Deleted old backup: " . basename($file));
            }
        }
    }
}
