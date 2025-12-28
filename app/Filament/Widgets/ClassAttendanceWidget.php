<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Absensi;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ClassAttendanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Kehadiran Per Kelas Hari Ini';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // ========== FIX: Struktur baru ==========
        $kelasList = Kelas::withCount('siswa') // Hitung jumlah siswa per kelas
            ->orderBy('tingkat')
            ->orderBy('urutan')
            ->get();

        $labels = collect();
        $hadir = collect();
        $tidakHadir = collect();

        foreach ($kelasList as $kelas) {
            // Label menggunakan id_kelas
            $labels->push($kelas->id_kelas);

            $totalSiswa = $kelas->siswa_count;
            
            // Hitung yang hadir hari ini
            $hadirCount = Absensi::whereDate('tanggal', today()) // Fix: date → tanggal
                ->where('id_kelas', $kelas->id_kelas) // Fix: pakai id_kelas
                ->where('status', 'HADIR') // Fix: present → HADIR
                ->count();

            $hadir->push($hadirCount);
            $tidakHadir->push($totalSiswa - $hadirCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadir->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                ],
                [
                    'label' => 'Tidak Hadir',
                    'data' => $tidakHadir->toArray(),
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
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
            ],
        ];
    }
}