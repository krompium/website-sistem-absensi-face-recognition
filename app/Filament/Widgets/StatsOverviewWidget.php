<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\IndikasiSiswa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today();
        
        // ========== FIX: Struktur baru tidak ada is_active di siswa & kelas ==========
        
        // Total Students
        $totalStudents = Siswa::count();
        
        // Today's attendance
        $todayAttendance = Absensi::whereDate('tanggal', $today)->count(); // Fix: date → tanggal
        
        $todayPresent = Absensi::whereDate('tanggal', $today)
            ->where('status', 'HADIR') // Fix: present → HADIR
            ->count();
        
        $todayLate = Absensi::whereDate('tanggal', $today)
            ->whereIn('status', ['HADIR']) // Di struktur baru tidak ada status "late", semua HADIR
            ->whereNotNull('jam_masuk')
            ->whereTime('jam_masuk', '>', '07:30:00') // Asumsi terlambat jika masuk > 07:30
            ->count();
        
        $todayAbsent = $totalStudents - $todayAttendance;
        
        // Attendance rate
        $attendanceRate = $totalStudents > 0 
            ? round(($todayPresent / $totalStudents) * 100, 1) 
            : 0;
        
        // ========== FIX: Drunk detections dari IndikasiSiswa ==========
        $drunkDetections = IndikasiSiswa::whereHas('absensi', function ($q) use ($today) {
                $q->whereDate('tanggal', $today);
            })
            ->where('final_decision', 'DRUNK INDICATION')
            ->count();
        
        // Active classes
        $activeClasses = Kelas::count(); // Fix: tidak ada is_active

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description($activeClasses . ' kelas')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $totalStudents]),
            
            Stat::make('Kehadiran Hari Ini', $todayPresent . '/' . $totalStudents)
                ->description($attendanceRate . '% tingkat kehadiran')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger'))
                ->chart([$todayAbsent, $todayLate, $todayPresent]),
            
            Stat::make('Tidak Hadir', $todayAbsent)
                ->description('Dari ' . $totalStudents . ' siswa')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($todayAbsent > 10 ? 'danger' : 'warning'),
            
            Stat::make('Deteksi Mabuk', $drunkDetections)
                ->description($drunkDetections > 0 ? 'Perlu tindakan segera!' : 'Tidak ada deteksi')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($drunkDetections > 0 ? 'danger' : 'success'),
        ];
    }
}