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
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
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

                Select::make('class_id')
                    ->label('Kelas (Opsional)')
                    ->options(Classes::pluck('name', 'id'))
                    ->placeholder('Semua Kelas')
                    ->visible(fn ($get) => in_array($get('report_type'), ['attendance', 'class_summary', 'student_detail'])),

                Select::make('student_id')
                    ->label('Siswa')
                    ->options(Student::where('is_active', true)->pluck('name', 'id'))
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

        // TODO: Implement actual report generation
        // For now, just show notification
        Notification::make()
            ->title('Laporan Sedang Diproses')
            ->body('Laporan ' . $data['report_type'] . ' akan segera tersedia untuk diunduh')
            ->success()
            ->send();

        // Simulate report generation
        // In real implementation, you would:
        // 1. Generate PDF/Excel using libraries like DomPDF, Laravel Excel
        // 2. Return download response
        // 3. Or send email with attachment
    }

    public function getReportPreview()
    {
        $data = $this->form->getState();
        
        if (!$data['start_date'] || !$data['end_date']) {
            return null;
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $query = Attendance::whereBetween('date', [$startDate, $endDate])
            ->with(['student', 'student.class']);

        if (!empty($data['class_id'])) {
            $query->whereHas('student', function($q) use ($data) {
                $q->where('class_id', $data['class_id']);
            });
        }

        if (!empty($data['student_id'])) {
            $query->where('student_id', $data['student_id']);
        }

        $attendances = $query->get();

        return [
            'total_records' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
            'date_range' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
        ];
    }
}