<?php

// ================================================================

// app/Exports/ClassSummaryExport.php

namespace App\Exports;

use App\Models\Classes;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Classes::with(['students' => function($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();
    }

    public function headings(): array
    {
        return [
            'Kelas',
            'Total Siswa',
            'Hadir',
            'Terlambat',
            'Tidak Hadir',
            'Sakit',
            'Izin',
            'Tingkat Kehadiran (%)',
        ];
    }

    public function map($class): array
    {
        $studentIds = $class->students->pluck('id');
        
        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get();

        $totalStudents = $class->students->count();
        $totalDays = $this->startDate->diffInDays($this->endDate) + 1;
        $expectedAttendances = $totalStudents * $totalDays;
        
        $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();
        $attendanceRate = $expectedAttendances > 0 ? round(($presentCount / $expectedAttendances) * 100, 2) : 0;

        return [
            $class->name,
            $totalStudents,
            $attendances->where('status', 'present')->count(),
            $attendances->where('status', 'late')->count(),
            $attendances->where('status', 'absent')->count(),
            $attendances->where('status', 'sick')->count(),
            $attendances->where('status', 'permission')->count(),
            $attendanceRate,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Kelas';
    }
}
