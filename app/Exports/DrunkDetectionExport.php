<?php

// ================================================================

// app/Exports/DrunkDetectionExport.php

namespace App\Exports;

use App\Models\Detection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrunkDetectionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return Detection::with(['student', 'student.class', 'attendance'])
            ->whereIn('drunk_status', ['suspected', 'drunk'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Status',
            'Confidence (%)',
            'Mata Merah',
            'Postur Tidak Stabil',
            'Tingkat Keparahan',
            'Notifikasi Terkirim',
        ];
    }

    public function map($detection): array
    {
        return [
            $detection->created_at->format('d/m/Y'),
            $detection->created_at->format('H:i'),
            $detection->student->nis,
            $detection->student->name,
            $detection->student->class->name,
            $detection->drunk_status === 'drunk' ? 'Mabuk' : 'Terindikasi',
            $detection->drunk_confidence,
            $detection->red_eyes ? 'Ya' : 'Tidak',
            $detection->unstable_posture ? 'Ya' : 'Tidak',
            match($detection->severity) {
                'high' => 'Tinggi',
                'medium' => 'Sedang',
                'low' => 'Rendah',
                default => '-',
            },
            $detection->notification_sent ? 'Ya' : 'Belum',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C00000']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Deteksi Mabuk';
    }
}