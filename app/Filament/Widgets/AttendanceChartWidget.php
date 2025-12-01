<?php
// app/Filament/Widgets/AttendanceChartWidget.php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Kehadiran 7 Hari Terakhir';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = collect();
        $present = collect();
        $late = collect();
        $absent = collect();

        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days->push($date->format('d/m'));

            // Count present
            $presentCount = Attendance::whereDate('date', $date)
                ->where('status', 'present')
                ->count();
            $present->push($presentCount);

            // Count late
            $lateCount = Attendance::whereDate('date', $date)
                ->where('status', 'late')
                ->count();
            $late->push($lateCount);

            // Count absent (sick + permission + absent)
            $absentCount = Attendance::whereDate('date', $date)
                ->whereIn('status', ['absent', 'sick', 'permission'])
                ->count();
            $absent->push($absentCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $present->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Terlambat',
                    'data' => $late->toArray(),
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Tidak Hadir',
                    'data' => $absent->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
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