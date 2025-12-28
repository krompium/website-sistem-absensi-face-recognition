<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Kehadiran 7 Hari Terakhir';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = collect();
        $hadir = collect();
        $izin = collect();
        $sakit = collect();
        $alpa = collect();

        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days->push($date->format('d/m'));

            // ========== FIX: Gunakan field & status baru ==========
            
            // Count HADIR
            $hadirCount = Absensi::whereDate('tanggal', $date) // Fix: date → tanggal
                ->where('status', 'HADIR') // Fix: present → HADIR
                ->count();
            $hadir->push($hadirCount);

            // Count IZIN
            $izinCount = Absensi::whereDate('tanggal', $date)
                ->where('status', 'IZIN')
                ->count();
            $izin->push($izinCount);

            // Count SAKIT
            $sakitCount = Absensi::whereDate('tanggal', $date)
                ->where('status', 'SAKIT')
                ->count();
            $sakit->push($sakitCount);

            // Count ALPA
            $alpaCount = Absensi::whereDate('tanggal', $date)
                ->where('status', 'ALPA')
                ->count();
            $alpa->push($alpaCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadir->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Izin',
                    'data' => $izin->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Sakit',
                    'data' => $sakit->toArray(),
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Alpa',
                    'data' => $alpa->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
            ],
        ];
    }
}