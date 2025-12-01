<?php
// app/Exports/AttendanceExport.php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $classId;

    public function __construct(Carbon $startDate, Carbon $endDate, ?int $classId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->classId = $classId;
    }

    public function collection()
    {
        $query = Attendance::with(['student', 'student.class'])
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        if ($this->classId) {
            $query->whereHas('student', function($q) {
                $q->where('class_id', $this->classId);
            });
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Jam Keluar',
            'Status',
            'Suhu (°C)',
            'Confidence (%)',
            'Keterangan',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->date->format('d/m/Y'),
            $attendance->student->nis,
            $attendance->student->name,
            $attendance->student->class->name,
            $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-',
            $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-',
            $this->getStatusLabel($attendance->status),
            $attendance->temperature ?? '-',
            $attendance->check_in_confidence ?? '-',
            $attendance->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Kehadiran';
    }

    protected function getStatusLabel($status): string
    {
        return match($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $status,
        };
    }
}