<?php
// app/Filament/Widgets/ClassAttendanceWidget.php

namespace App\Filament\Widgets;

use App\Models\Classes;
use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ClassAttendanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Kehadiran Per Kelas Hari Ini';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $classes = Classes::where('is_active', true)
            ->with(['students' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();

        $labels = collect();
        $present = collect();
        $absent = collect();

        foreach ($classes as $class) {
            $labels->push($class->name);

            $totalStudents = $class->students->count();
            
            $presentCount = Attendance::whereDate('date', today())
                ->whereIn('student_id', $class->students->pluck('id'))
                ->whereIn('status', ['present', 'late'])
                ->count();

            $present->push($presentCount);
            $absent->push($totalStudents - $presentCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $present->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                ],
                [
                    'label' => 'Tidak Hadir',
                    'data' => $absent->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}