{{-- resources/views/emails/drunk-detection-alert.blade.php --}}

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
        .alert-header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .alert-icon {
            font-size: 3em;
        }
        .content {
            padding: 20px;
        }
        .alert-box {
            background: #fee;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
        }
        .info-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            font-size: 1.1em;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="alert-header">
        <div class="alert-icon">⚠️</div>
        <h2>ALERT: DETEKSI MABUK</h2>
        <p>Tindakan segera diperlukan</p>
    </div>

    <div class="content">
        <div class="alert-box">
            <strong>PERINGATAN PENTING!</strong><br>
            Sistem telah mendeteksi indikasi mabuk pada siswa. 
            Mohon tindak lanjut segera dari pihak sekolah.
        </div>

        <h3>Informasi Siswa</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Nama Siswa</div>
                <div class="value">{{ $student->name }}</div>
            </div>
            <div class="info-item">
                <div class="label">NIS</div>
                <div class="value">{{ $student->nis }}</div>
            </div>
            <div class="info-item">
                <div class="label">Kelas</div>
                <div class="value">{{ $student->class->name }}</div>
            </div>
            <div class="info-item">
                <div class="label">Waktu Deteksi</div>
                <div class="value">{{ $detection->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <h3>Detail Deteksi</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Status</div>
                <div class="value" style="color: #dc2626;">
                    {{ $detection->drunk_status === 'drunk' ? 'MABUK' : 'TERINDIKASI' }}
                </div>
            </div>
            <div class="info-item">
                <div class="label">Confidence Score</div>
                <div class="value">{{ $detection->drunk_confidence }}%</div>
            </div>
            <div class="info-item">
                <div class="label">Tingkat Keparahan</div>
                <div class="value">
                    {{ strtoupper($detection->severity) }}
                </div>
            </div>
            <div class="info-item">
                <div class="label">Indikator</div>
                <div class="value">
                    @if($detection->red_eyes) • Mata Merah<br> @endif
                    @if($detection->unstable_posture) • Postur Tidak Stabil @endif
                </div>
            </div>
        </div>

        <h3>Tindakan yang Disarankan</h3>
        <ul>
            <li>Panggil siswa untuk verifikasi lebih lanjut</li>
            <li>Hubungi orang tua/wali siswa</li>
            <li>Dokumentasikan kejadian</li>
            <li>Lakukan konseling jika diperlukan</li>
        </ul>
    </div>

    <div class="footer">
        <p><strong>Email ini memerlukan tindakan segera!</strong></p>
        <p>{{ config('app.school_name') }}</p>
        <p>Telp: {{ config('app.school_phone') }}</p>
    </div>
</body>
</html>