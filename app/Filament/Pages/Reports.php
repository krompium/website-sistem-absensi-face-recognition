<?php
// app/Filament/Pages/Reports.php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\Kelas;
use Carbon\Carbon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'start_date' => today()->startOfMonth(),
            'end_date' => today(),
            'report_type' => 'attendance',
            'format' => 'pdf',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->label('Jenis Laporan')
                    ->options([
                        'attendance' => 'Laporan Kehadiran',
                        'class_summary' => 'Ringkasan Per Kelas',
                        'student_detail' => 'Detail Per Siswa',
                        'drunk_detection' => 'Laporan Deteksi Mabuk',
                        'late_report' => 'Laporan Keterlambatan',
                    ])
                    ->required()
                    ->reactive(),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->maxDate(fn ($get) => $get('end_date') ?? today()),

                DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->required()
                    ->minDate(fn ($get) => $get('start_date'))
                    ->maxDate(today()),

                Select::make('id_kelas')
                    ->label('Kelas (Opsional)')
                    ->options(function () {
                        return Kelas::all()->mapWithKeys(function ($kelas) {
                            return [$kelas->id_kelas => $kelas->getFullName()];
                        });
                    })
                    ->placeholder('Semua Kelas')
                    ->searchable()
                    ->visible(fn ($get) => in_array($get('report_type'), ['attendance', 'class_summary', 'student_detail'])),

                Select::make('kode_siswa')
                    ->label('Siswa')
                    ->options(function () {
                        return Siswa::all()->mapWithKeys(function ($siswa) {
                            return [$siswa->kode_siswa => $siswa->nama_siswa . ' (' . $siswa->kode_siswa . ')'];
                        });
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn ($get) => $get('report_type') === 'student_detail'),

                Select::make('format')
                    ->label('Format Export')
                    ->options([
                        'pdf' => 'PDF',
                        'excel' => 'Excel',
                        'csv' => 'CSV',
                    ])
                    ->default('pdf')
                    ->required(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();

        // Validate
        if (empty($data['start_date']) || empty($data['end_date'])) {
            Notification::make()
                ->title('Error')
                ->body('Tanggal mulai dan akhir harus diisi')
                ->danger()
                ->send();
            return;
        }

        // Get report type label
        $reportTypeLabels = [
            'attendance' => 'Kehadiran',
            'class_summary' => 'Ringkasan Per Kelas',
            'student_detail' => 'Detail Per Siswa',
            'drunk_detection' => 'Deteksi Mabuk',
            'late_report' => 'Keterlambatan',
        ];

        $reportLabel = $reportTypeLabels[$data['report_type']] ?? $data['report_type'];

        // TODO: Implement actual report generation
        // For now, just show notification
        Notification::make()
            ->title('Laporan Sedang Diproses')
            ->body('Laporan ' . $reportLabel . ' akan segera tersedia untuk diunduh')
            ->success()
            ->send();

        // Simulate report generation
        // In real implementation, you would:
        // 1. Generate PDF/Excel using libraries like DomPDF, Laravel Excel
        // 2. Return download response
        // 3. Or send email with attachment
        
        // Example implementation:
        // return $this->downloadReport($data);
    }

    protected function downloadReport(array $data)
    {
        // TODO: Implement actual download logic
        // Example for PDF:
        // $pdf = PDF::loadView('reports.attendance', [
        //     'data' => $this->getReportData($data)
        // ]);
        // return response()->streamDownload(function() use ($pdf) {
        //     echo $pdf->output();
        // }, 'laporan-' . $data['report_type'] . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getReportPreview()
    {
        $data = $this->form->getState();
        
        if (!$data['start_date'] || !$data['end_date']) {
            return null;
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $query = Absensi::whereBetween('tanggal', [$startDate, $endDate])
            ->with(['siswa', 'siswa.kelas']);

        // Filter by class if selected
        if (!empty($data['id_kelas'])) {
            $query->where('id_kelas', $data['id_kelas']);
        }

        // Filter by student if selected
        if (!empty($data['kode_siswa'])) {
            $query->where('kode_siswa', $data['kode_siswa']);
        }

        $absensis = $query->get();

        // Calculate statistics based on new status values
        $hadir = $absensis->where('status', 'HADIR')->count();
        $izin = $absensis->where('status', 'IZIN')->count();
        $sakit = $absensis->where('status', 'SAKIT')->count();
        $alpa = $absensis->where('status', 'ALPA')->count();

        // Count drunk detections
        $drunkDetections = $absensis->filter(function ($absensi) {
            return $absensi->indikasiSiswa()->where('final_decision', 'MABUK')->exists();
        })->count();

        // Calculate late attendances (jam_masuk after 07:30)
        $late = $absensis->filter(function ($absensi) {
            if (!$absensi->jam_masuk) return false;
            $checkInTime = Carbon::parse($absensi->jam_masuk);
            $lateThreshold = Carbon::parse('07:30:00');
            return $checkInTime->gt($lateThreshold);
        })->count();

        return [
            'total_records' => $absensis->count(),
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'terlambat' => $late,
            'deteksi_mabuk' => $drunkDetections,
            'date_range' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'percentage' => [
                'hadir' => $absensis->count() > 0 ? round(($hadir / $absensis->count()) * 100, 1) : 0,
                'tidak_hadir' => $absensis->count() > 0 ? round((($izin + $sakit + $alpa) / $absensis->count()) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get detailed report data based on report type
     */
    protected function getReportData(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date']);
        $endDate = Carbon::parse($filters['end_date']);

        switch ($filters['report_type']) {
            case 'attendance':
                return $this->getAttendanceReport($startDate, $endDate, $filters);
            
            case 'class_summary':
                return $this->getClassSummaryReport($startDate, $endDate, $filters);
            
            case 'student_detail':
                return $this->getStudentDetailReport($startDate, $endDate, $filters);
            
            case 'drunk_detection':
                return $this->getDrunkDetectionReport($startDate, $endDate, $filters);
            
            case 'late_report':
                return $this->getLateReport($startDate, $endDate, $filters);
            
            default:
                return [];
        }
    }

    protected function getAttendanceReport($startDate, $endDate, $filters): array
    {
        $query = Absensi::whereBetween('tanggal', [$startDate, $endDate])
            ->with(['siswa', 'siswa.kelas']);

        if (!empty($filters['id_kelas'])) {
            $query->where('id_kelas', $filters['id_kelas']);
        }

        return [
            'absensis' => $query->get(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function getClassSummaryReport($startDate, $endDate, $filters): array
    {
        $kelasQuery = Kelas::query();
        
        if (!empty($filters['id_kelas'])) {
            $kelasQuery->where('id_kelas', $filters['id_kelas']);
        }

        $kelasList = $kelasQuery->get();
        $summary = [];

        foreach ($kelasList as $kelas) {
            $absensis = Absensi::whereBetween('tanggal', [$startDate, $endDate])
                ->where('id_kelas', $kelas->id_kelas)
                ->get();

            $summary[] = [
                'kelas' => $kelas->getFullName(),
                'total_siswa' => $kelas->siswa()->count(),
                'total_absensi' => $absensis->count(),
                'hadir' => $absensis->where('status', 'HADIR')->count(),
                'izin' => $absensis->where('status', 'IZIN')->count(),
                'sakit' => $absensis->where('status', 'SAKIT')->count(),
                'alpa' => $absensis->where('status', 'ALPA')->count(),
            ];
        }

        return [
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function getStudentDetailReport($startDate, $endDate, $filters): array
    {
        $siswa = Siswa::where('kode_siswa', $filters['kode_siswa'])->first();
        
        if (!$siswa) {
            return [];
        }

        $absensis = Absensi::where('kode_siswa', $siswa->kode_siswa)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('indikasiSiswa')
            ->get();

        return [
            'siswa' => $siswa,
            'absensis' => $absensis,
            'statistics' => [
                'total' => $absensis->count(),
                'hadir' => $absensis->where('status', 'HADIR')->count(),
                'izin' => $absensis->where('status', 'IZIN')->count(),
                'sakit' => $absensis->where('status', 'SAKIT')->count(),
                'alpa' => $absensis->where('status', 'ALPA')->count(),
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function getDrunkDetectionReport($startDate, $endDate, $filters): array
    {
        $query = Absensi::whereBetween('tanggal', [$startDate, $endDate])
            ->with(['siswa', 'siswa.kelas', 'indikasiSiswa'])
            ->whereHas('indikasiSiswa', function ($q) {
                $q->where('final_decision', 'MABUK');
            });

        if (!empty($filters['id_kelas'])) {
            $query->where('id_kelas', $filters['id_kelas']);
        }

        return [
            'detections' => $query->get(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function getLateReport($startDate, $endDate, $filters): array
    {
        $query = Absensi::whereBetween('tanggal', [$startDate, $endDate])
            ->with(['siswa', 'siswa.kelas'])
            ->whereNotNull('jam_masuk');

        if (!empty($filters['id_kelas'])) {
            $query->where('id_kelas', $filters['id_kelas']);
        }

        $absensis = $query->get()->filter(function ($absensi) {
            $checkInTime = Carbon::parse($absensi->jam_masuk);
            $lateThreshold = Carbon::parse('07:30:00');
            return $checkInTime->gt($lateThreshold);
        });

        return [
            'late_records' => $absensis,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}