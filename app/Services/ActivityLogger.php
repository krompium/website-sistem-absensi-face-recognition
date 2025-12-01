<?php
// app/Services/ActivityLogger.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    /**
     * Log user activity
     */
    public function log(string $action, string $description, array $data = [], string $level = 'info'): void
    {
        $logData = [
            'action' => $action,
            'description' => $description,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        // Log to Laravel log
        Log::channel('activity')->{$level}($description, $logData);

        // Optionally store in database
        if (config('logging.store_in_database', false)) {
            $this->storeInDatabase($logData);
        }
    }

    /**
     * Log attendance activity
     */
    public function logAttendance(string $action, $attendance, array $additional = []): void
    {
        $this->log(
            action: "attendance.{$action}",
            description: "Attendance {$action}: {$attendance->student->name}",
            data: array_merge([
                'attendance_id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'student_name' => $attendance->student->name,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
            ], $additional)
        );
    }

    /**
     * Log drunk detection
     */
    public function logDrunkDetection($detection): void
    {
        $this->log(
            action: 'detection.drunk',
            description: "Drunk detection: {$detection->student->name} - {$detection->drunk_status}",
            data: [
                'detection_id' => $detection->id,
                'student_id' => $detection->student_id,
                'student_name' => $detection->student->name,
                'drunk_status' => $detection->drunk_status,
                'confidence' => $detection->drunk_confidence,
                'severity' => $detection->severity,
            ],
            level: 'warning'
        );
    }

    /**
     * Log API request
     */
    public function logApiRequest(string $endpoint, array $data = [], ?string $status = null): void
    {
        $this->log(
            action: 'api.request',
            description: "API Request: {$endpoint}",
            data: [
                'endpoint' => $endpoint,
                'method' => request()->method(),
                'status' => $status,
                'request_data' => $data,
            ]
        );
    }

    /**
     * Log notification sent
     */
    public function logNotification($notification, bool $success): void
    {
        $this->log(
            action: 'notification.sent',
            description: "Notification sent: {$notification->type} to {$notification->recipient_name}",
            data: [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'recipient' => $notification->recipient_name,
                'status' => $success ? 'success' : 'failed',
            ],
            level: $success ? 'info' : 'error'
        );
    }

    /**
     * Log system error
     */
    public function logError(string $context, \Throwable $exception): void
    {
        $this->log(
            action: 'system.error',
            description: "Error in {$context}: {$exception->getMessage()}",
            data: [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            level: 'error'
        );
    }

    /**
     * Store activity in database
     */
    protected function storeInDatabase(array $data): void
    {
        try {
            DB::table('activity_logs')->insert([
                'action' => $data['action'],
                'description' => $data['description'],
                'user_id' => $data['user_id'],
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'data' => json_encode($data['data']),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store activity log in database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 50): array
    {
        if (!config('logging.store_in_database', false)) {
            return [];
        }

        return DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get activity statistics
     */
    public function getStatistics(string $period = 'today'): array
    {
        if (!config('logging.store_in_database', false)) {
            return [];
        }

        $query = DB::table('activity_logs');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        return [
            'total' => $query->count(),
            'by_action' => $query->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->get()
                ->pluck('count', 'action')
                ->toArray(),
        ];
    }
}
