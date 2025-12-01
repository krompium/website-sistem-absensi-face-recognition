<?php
// app/Filament/Pages/RealtimeMonitor.php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class RealtimeMonitor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-tv';
    protected static ?string $navigationLabel = 'Monitor Real-time';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.realtime-monitor';

    public ?array $data = [];
    public $date;
    public $classId;

    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
        $this->form->fill([
            'date' => $this->date,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->default(today())
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->date = $state),
                
                Select::make('class_id')
                    ->label('Filter Kelas')
                    ->options(\App\Models\Classes::pluck('name', 'id'))
                    ->placeholder('Semua Kelas')
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->classId = $state),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getAttendanceData()
    {
        $query = Attendance::with(['student', 'student.class'])
            ->whereDate('date', $this->date ?? today());

        if ($this->classId) {
            $query->whereHas('student', function($q) {
                $q->where('class_id', $this->classId);
            });
        }

        return $query->latest('check_in_time')->get();
    }

    public function getStats()
    {
        $date = $this->date ?? today();
        $query = Attendance::whereDate('date', $date);

        if ($this->classId) {
            $query->whereHas('student', function($q) {
                $q->where('class_id', $this->classId);
            });
        }

        $total = $query->count();
        $present = (clone $query)->where('status', 'present')->count();
        $late = (clone $query)->where('status', 'late')->count();
        $absent = Student::where('is_active', true)
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->count() - $total;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'percentage' => $total > 0 ? round(($present / ($total + $absent)) * 100, 1) : 0,
        ];
    }
}