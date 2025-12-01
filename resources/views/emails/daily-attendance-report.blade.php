{{-- resources/views/emails/daily-attendance-report.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            background-color: #4472C4;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #4472C4;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4472C4;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.school_name', 'SEKOLAH') }}</h2>
        <h3>Laporan Kehadiran Harian</h3>
        <p>{{ $date->format('l, d F Y') }}</p>
    </div>

    <div class="content">
        <h3>Ringkasan Kehadiran</h3>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Absensi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;">{{ $stats['present'] }}</div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f59e0b;">{{ $stats['late'] }}</div>
                <div class="stat-label">Terlambat</div>
            </div>
        </div>

        <h3>Detail Kehadiran</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $index => $attendance)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $attendance->student->nis }}</td>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->student->class->name }}</td>
                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                        <td>
                            @if($attendance->status === 'present')
                                <span style="color: #10b981;">✓ Hadir</span>
                            @elseif($attendance->status === 'late')
                                <span style="color: #f59e0b;">⏰ Terlambat</span>
                            @else
                                {{ $attendance->status }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Email ini digenerate otomatis oleh Sistem Absensi Face Recognition</p>
        <p>{{ config('app.school_name') }} - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>
