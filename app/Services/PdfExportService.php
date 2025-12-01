<?php
// app/Services/PdfExportService.php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Detection;
use App\Models\Classes;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportService
{
    /**
     * Export attendance report to PDF
     */
    public function exportAttendanceReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $classId = null
    ) {
        $query = Attendance::with(['student', 'student.class'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
        ];

        $data = [
            'title' => 'Laporan Kehadiran Siswa',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'class' => $classId ? Classes::find($classId)->name : 'Semua Kelas',
            'generated_at' => now()->format('d/m/Y H:i'),
            'attendances' => $attendances,
            'stats' => $stats,
        ];

        $pdf = Pdf::loadView('pdf.attendance-report', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan-kehadiran-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export class summary report
     */
    public function exportClassSummary(Carbon $startDate, Carbon $endDate)
    {
        $classes = Classes::with(['students' => function($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();

        $classSummary = [];

        foreach ($classes as $class) {
            $studentIds = $class->students->pluck('id');
            
            $attendances = Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $classSummary[] = [
                'class_name' => $class->name,
                'total_students' => $class->students->count(),
                'total_attendances' => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'attendance_rate' => $this->calculateAttendanceRate($attendances, $class->students->count()),
            ];
        }

        $data = [
            'title' => 'Ringkasan Kehadiran Per Kelas',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'summary' => $classSummary,
        ];

        $pdf = Pdf::loadView('pdf.class-summary', $data);
        
        return $pdf->download('ringkasan-kelas-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export student detail report
     */
    public function exportStudentDetail(int $studentId, Carbon $startDate, Carbon $endDate)
    {
        $student = Student::with('class')->findOrFail($studentId);
        
        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $detections = Detection::where('student_id', $studentId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('attendance')
            ->get();

        $stats = [
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'total_attendances' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
            'permission' => $attendances->where('status', 'permission')->count(),
            'drunk_detections' => $detections->whereIn('drunk_status', ['suspected', 'drunk'])->count(),
            'attendance_rate' => $this->calculateAttendanceRate($attendances, $startDate->diffInDays($endDate) + 1),
        ];

        $data = [
            'title' => 'Laporan Detail Siswa',
            'student' => $student,
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'attendances' => $attendances,
            'detections' => $detections,
            'stats' => $stats,
        ];

        $pdf = Pdf::loadView('pdf.student-detail', $data);
        
        return $pdf->download('laporan-siswa-' . $student->nis . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export drunk detection report
     */
    public function exportDrunkDetectionReport(Carbon $startDate, Carbon $endDate)
    {
        $detections = Detection::with(['student', 'student.class', 'attendance'])
            ->whereIn('drunk_status', ['suspected', 'drunk'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $detections->count(),
            'drunk' => $detections->where('drunk_status', 'drunk')->count(),
            'suspected' => $detections->where('drunk_status', 'suspected')->count(),
            'high_severity' => $detections->where('severity', 'high')->count(),
            'notification_sent' => $detections->where('notification_sent', true)->count(),
        ];

        $data = [
            'title' => 'Laporan Deteksi Mabuk',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'detections' => $detections,
            'stats' => $stats,
        ];

        $pdf = Pdf::loadView('pdf.drunk-detection-report', $data);
        
        return $pdf->download('laporan-deteksi-mabuk-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Calculate attendance rate
     */
    protected function calculateAttendanceRate($attendances, int $totalDays): float
    {
        if ($totalDays == 0) return 0;
        
        $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();
        
        return round(($presentCount / $totalDays) * 100, 2);
    }
}