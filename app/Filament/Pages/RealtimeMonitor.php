<?php
// app/Filament/Pages/RealtimeMonitor.php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\Kelas;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Carbon\Carbon;

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
    public $kelasId;

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
                
                Select::make('id_kelas')
                    ->label('Filter Kelas')
                    ->options(function () {
                        return Kelas::all()->mapWithKeys(function ($kelas) {
                            return [$kelas->id_kelas => $kelas->getFullName()];
                        });
                    })
                    ->placeholder('Semua Kelas')
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->kelasId = $state),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getAttendanceData()
    {
        $query = Absensi::with(['siswa', 'siswa.kelas'])
            ->whereDate('tanggal', $this->date ?? today())
            ->whereNotNull('jam_masuk'); // Only show checked-in attendance

        if ($this->kelasId) {
            $query->where('id_kelas', $this->kelasId);
        }

        return $query->latest('jam_masuk')->get();
    }

    public function getStats()
    {
        $date = $this->date ?? today();
        $query = Absensi::whereDate('tanggal', $date);

        if ($this->kelasId) {
            $query->where('id_kelas', $this->kelasId);
        }

        $allAbsensis = $query->get();
        $total = $allAbsensis->count();
        
        // Hadir (status HADIR)
        $hadir = $allAbsensis->where('status', 'HADIR')->count();
        
        // Terlambat (jam_masuk > 07:30)
        $terlambat = $allAbsensis->filter(function ($absensi) {
            if (!$absensi->jam_masuk) return false;
            $checkInTime = Carbon::parse($absensi->jam_masuk);
            $lateThreshold = Carbon::parse('07:30:00');
            return $checkInTime->gt($lateThreshold) && $absensi->status === 'HADIR';
        })->count();
        
        // Tepat waktu (hadir - terlambat)
        $tepatWaktu = $hadir - $terlambat;
        
        // Izin, Sakit, Alpa
        $izin = $allAbsensis->where('status', 'IZIN')->count();
        $sakit = $allAbsensis->where('status', 'SAKIT')->count();
        $alpa = $allAbsensis->where('status', 'ALPA')->count();
        
        // Total siswa yang tidak hadir (izin + sakit + alpa)
        $tidakHadir = $izin + $sakit + $alpa;
        
        // Total siswa berdasarkan kelas
        $totalSiswaQuery = Siswa::query();
        if ($this->kelasId) {
            $totalSiswaQuery->where('id_kelas', $this->kelasId);
        }
        $totalSiswa = $totalSiswaQuery->count();
        
        // Siswa yang belum absen
        $belumAbsen = $totalSiswa - $total;
        
        // Calculate percentage (hadir / total siswa * 100)
        $percentage = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0;

        return [
            'total' => $total,
            'hadir' => $hadir,
            'tepat_waktu' => $tepatWaktu,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'tidak_hadir' => $tidakHadir,
            'belum_absen' => $belumAbsen,
            'total_siswa' => $totalSiswa,
            'percentage' => $percentage,
        ];
    }

    /**
     * Determine if attendance is late based on check-in time
     */
    protected function isLate($jamMasuk): bool
    {
        if (!$jamMasuk) return false;
        
        $checkInTime = Carbon::parse($jamMasuk);
        $lateThreshold = Carbon::parse('07:30:00');
        
        return $checkInTime->gt($lateThreshold);
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabel($status): string
    {
        return match($status) {
            'HADIR' => 'Hadir',
            'IZIN' => 'Izin',
            'SAKIT' => 'Sakit',
            'ALPA' => 'Alpa',
            default => $status,
        };
    }

    /**
     * Get status color
     */
    public function getStatusColor($status): string
    {
        return match($status) {
            'HADIR' => 'green',
            'IZIN' => 'gray',
            'SAKIT' => 'purple',
            'ALPA' => 'red',
            default => 'gray',
        };
    }
}