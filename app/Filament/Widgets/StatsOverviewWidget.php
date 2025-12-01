<?php
// app/Filament/Widgets/StatsOverviewWidget.php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Detection;
use App\Models\Classes;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today();
        
        // Total Students
        $totalStudents = Student::where('is_active', true)->count();
        
        // Today's attendance
        $todayAttendance = Attendance::whereDate('date', $today)->count();
        $todayPresent = Attendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $todayLate = Attendance::whereDate('date', $today)
            ->where('status', 'late')
            ->count();
        $todayAbsent = $totalStudents - $todayAttendance;
        
        // Attendance rate
        $attendanceRate = $totalStudents > 0 
            ? round(($todayPresent / $totalStudents) * 100, 1) 
            : 0;
        
        // Drunk detections today
        $drunkDetections = Detection::whereDate('created_at', $today)
            ->whereIn('drunk_status', ['suspected', 'drunk'])
            ->count();
        
        // Active classes
        $activeClasses = Classes::where('is_active', true)->count();

        return [
            Stat::make('Total Siswa Aktif', $totalStudents)
                ->description($activeClasses . ' kelas aktif')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $totalStudents]),
            
            Stat::make('Kehadiran Hari Ini', $todayPresent . '/' . $totalStudents)
                ->description($attendanceRate . '% tingkat kehadiran')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger'))
                ->chart([$todayAbsent, $todayLate, $todayPresent]),
            
            Stat::make('Terlambat Hari Ini', $todayLate)
                ->description('Dari ' . $todayAttendance . ' yang hadir')
                ->descriptionIcon('heroicon-m-clock')
                ->color($todayLate > 5 ? 'warning' : 'success'),
            
            Stat::make('Deteksi Mabuk', $drunkDetections)
                ->description($drunkDetections > 0 ? 'Perlu tindakan segera!' : 'Tidak ada deteksi')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($drunkDetections > 0 ? 'danger' : 'success'),
        ];
    }
}