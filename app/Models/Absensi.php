<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Absensi extends Model
{
    use HasFactory;

    /**
     * ========== STRUKTUR BARU ==========
     * - Nama tabel: absensi
     * - Primary Key: id_absensi (auto-increment)
     * - Status: HADIR/IZIN/SAKIT/ALPA
     * - Jam: DATETIME (bukan TIME)
     * - Deteksi mabuk: di tabel terpisah (_indikasi_siswa)
     * ===================================
     */

    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';
    public $timestamps = false; // Hanya ada created_at

    protected $fillable = [
        'kode_siswa',
        'id_kelas',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
    ];

    protected $appends = [
        'status_label',
    ];

    // ========== RELATIONSHIPS ==========

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'kode_siswa', 'kode_siswa');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function indikasi(): HasMany
    {
        return $this->hasMany(IndikasiSiswa::class, 'id_absensi', 'id_absensi');
    }

    // ========== ACCESSORS ==========

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'HADIR' => 'Hadir',
            'IZIN' => 'Izin',
            'SAKIT' => 'Sakit',
            'ALPA' => 'Tanpa Keterangan',
            default => 'Unknown',
        };
    }

    // ========== HELPER METHODS ==========

    public function isHadir(): bool
    {
        return $this->status === 'HADIR';
    }

    public function isIzin(): bool
    {
        return $this->status === 'IZIN';
    }

    public function isSakit(): bool
    {
        return $this->status === 'SAKIT';
    }

    public function isAlpa(): bool
    {
        return $this->status === 'ALPA';
    }

    public function sudahMasuk(): bool
    {
        return !is_null($this->jam_masuk);
    }

    public function sudahKeluar(): bool
    {
        return !is_null($this->jam_keluar);
    }

    public function getDurasiMenit(): ?int
    {
        if (!$this->sudahMasuk() || !$this->sudahKeluar()) {
            return null;
        }

        return $this->jam_masuk->diffInMinutes($this->jam_keluar);
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'HADIR' => 'success',
            'IZIN' => 'info',
            'SAKIT' => 'warning',
            'ALPA' => 'danger',
            default => 'secondary',
        };
    }

    // Deteksi Mabuk Helpers

    public function getIndikasiMasuk(): ?IndikasiSiswa
    {
        return $this->indikasi()
            ->where('attendance_type', 'in')
            ->first();
    }

    public function getIndikasiKeluar(): ?IndikasiSiswa
    {
        return $this->indikasi()
            ->where('attendance_type', 'out')
            ->first();
    }

    public function adaIndikasiMabuk(): bool
    {
        return $this->indikasi()
            ->where('final_decision', 'DRUNK INDICATION')
            ->exists();
    }

    public function getJumlahIndikasiMabuk(): int
    {
        return $this->indikasi()
            ->where('final_decision', 'DRUNK INDICATION')
            ->count();
    }

    // ========== SCOPES ==========

    public function scopeHadir($query)
    {
        return $query->where('status', 'HADIR');
    }

    public function scopeIzin($query)
    {
        return $query->where('status', 'IZIN');
    }

    public function scopeSakit($query)
    {
        return $query->where('status', 'SAKIT');
    }

    public function scopeAlpa($query)
    {
        return $query->where('status', 'ALPA');
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeMingguIni($query)
    {
        return $query->whereBetween('tanggal', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
                     ->whereYear('tanggal', now()->year);
    }

    public function scopeRentangTanggal($query, $dari, $sampai)
    {
        return $query->whereBetween('tanggal', [$dari, $sampai]);
    }

    public function scopeBySiswa($query, string $kodeSiswa)
    {
        return $query->where('kode_siswa', $kodeSiswa);
    }

    public function scopeByKelas($query, string $idKelas)
    {
        return $query->where('id_kelas', $idKelas);
    }

    public function scopeWithIndikasiMabuk($query)
    {
        return $query->whereHas('indikasi', function ($q) {
            $q->where('final_decision', 'DRUNK INDICATION');
        });
    }

    // ========== GLOBAL SCOPES ==========
    
    protected static function booted()
    {
        static::addGlobalScope('guruAccess', function ($query) {
            $user = auth()->user();
            
            if ($user && $user->isGuru()) {
                $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
                $query->whereIn('id_kelas', $kelasIds);
            }
        });
    }
}
