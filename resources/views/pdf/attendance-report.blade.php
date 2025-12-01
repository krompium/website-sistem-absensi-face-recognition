{{-- resources/views/pdf/attendance-report.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        .stats {
            margin: 15px 0;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stat-item {
            display: table-cell;
            padding: 5px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status-present { color: #10b981; font-weight: bold; }
        .status-late { color: #f59e0b; font-weight: bold; }
        .status-absent { color: #ef4444; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.school_name', 'SEKOLAH') }}</h2>
        <h3>{{ $title }}</h3>
    </div>

    <div class="info">
        <p><strong>Periode:</strong> {{ $period }}</p>
        <p><strong>Kelas:</strong> {{ $class }}</p>
        <p><strong>Dicetak:</strong> {{ $generated_at }}</p>
    </div>

    <div class="stats">
        <h4>Statistik Kehadiran</h4>
        <div class="stats-grid">
            <div class="stat-item">
                <strong>Total</strong><br>{{ $stats['total'] }}
            </div>
            <div class="stat-item">
                <strong style="color: #10b981;">Hadir</strong><br>{{ $stats['present'] }}
            </div>
            <div class="stat-item">
                <strong style="color: #f59e0b;">Terlambat</strong><br>{{ $stats['late'] }}
            </div>
            <div class="stat-item">
                <strong style="color: #ef4444;">Tidak Hadir</strong><br>{{ $stats['absent'] }}
            </div>
            <div class="stat-item">
                <strong>Sakit</strong><br>{{ $stats['sick'] }}
            </div>
            <div class="stat-item">
                <strong>Izin</strong><br>{{ $stats['permission'] }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 12%;">NIS</th>
                <th style="width: 25%;">Nama Siswa</th>
                <th style="width: 15%;">Kelas</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 18%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->date->format('d/m/Y') }}</td>
                    <td>{{ $attendance->student->nis }}</td>
                    <td>{{ $attendance->student->name }}</td>
                    <td>{{ $attendance->student->class->name }}</td>
                    <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                    <td class="status-{{ $attendance->status }}">
                        {{ match($attendance->status) {
                            'present' => 'Hadir',
                            'late' => 'Terlambat',
                            'absent' => 'Tidak Hadir',
                            'sick' => 'Sakit',
                            'permission' => 'Izin',
                            default => $attendance->status
                        } }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh Sistem Absensi Face Recognition</p>
        <p>{{ config('app.school_name') }} - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>