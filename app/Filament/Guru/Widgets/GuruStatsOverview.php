<?php

namespace App\Filament\Guru\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\IndikasiSiswa;

class GuruStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');

        // Total kelas yang diajar
        $totalKelas = $kelasIds->count();

        // Total siswa di kelas yang diajar
        $totalSiswa = Siswa::whereIn('id_kelas', $kelasIds)->count();

        // Siswa hadir hari ini
        $hadirHariIni = Absensi::whereIn('id_kelas', $kelasIds)
            ->whereDate('tanggal', today())
            ->where('status', 'HADIR')
            ->count();

        // Siswa dengan indikasi bermasalah (7 hari terakhir)
        $indikasiMabuk = IndikasiSiswa::where('final_decision', 'DRUNK INDICATION')
            ->whereHas('absensi', function ($query) use ($kelasIds) {
                $query->whereIn('id_kelas', $kelasIds)
                    ->whereDate('tanggal', '>=', now()->subDays(7));
            })
            ->count();

        return [
            Stat::make('Kelas yang Diajar', $totalKelas)
                ->description('Total kelas yang Anda ajar')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make('Total Siswa', $totalSiswa)
                ->description('Siswa di kelas yang Anda ajar')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Hadir Hari Ini', $hadirHariIni)
                ->description('Siswa yang hadir hari ini')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Indikasi Bermasalah', $indikasiMabuk)
                ->description('7 hari terakhir')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($indikasiMabuk > 0 ? 'danger' : 'success'),
        ];
    }
}
