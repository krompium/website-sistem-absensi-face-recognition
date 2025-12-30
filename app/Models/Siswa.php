<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Siswa extends Model
{
    use HasFactory;

    /**
     * ========== STRUKTUR BARU ==========
     * - Nama tabel: siswa
     * - Primary Key: kode_siswa (VARCHAR)
     * - No auto-increment
     * - Tambahan: jenis_kelamin, tanggal_lahir
     * ===================================
     */
    
    protected $table = 'siswa';
    protected $primaryKey = 'kode_siswa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Hanya ada created_at
    
    protected $fillable = [
        'kode_siswa',
        'nama_siswa',
        'nomor_wali',
        'id_kelas',
        'jenis_kelamin',
        'tanggal_lahir',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    protected $appends = [
        'umur',
        'jenis_kelamin_label',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'kode_siswa', 'kode_siswa');
    }

    // ========== ACCESSORS ==========
    
    public function getUmurAttribute(): int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : 0;
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    // ========== HELPER METHODS ==========
    
    public function getAbsensiHariIni(): ?Absensi
    {
        return $this->absensi()
            ->whereDate('tanggal', today())
            ->first();
    }

    public function sudahAbsenHariIni(): bool
    {
        return $this->absensi()
            ->whereDate('tanggal', today())
            ->exists();
    }

    public function getPersentaseKehadiran(int $days = 30): float
    {
        $totalAbsensi = $this->absensi()
            ->where('tanggal', '>=', now()->subDays($days))
            ->count();
        
        $hadir = $this->absensi()
            ->where('tanggal', '>=', now()->subDays($days))
            ->where('status', 'HADIR')
            ->count();
        
        return $totalAbsensi > 0 ? round(($hadir / $totalAbsensi) * 100, 2) : 0;
    }

    public function getRingkasanAbsensi(Carbon $dari = null, Carbon $sampai = null): array
    {
        $query = $this->absensi();
        
        if ($dari) {
            $query->where('tanggal', '>=', $dari);
        }
        
        if ($sampai) {
            $query->where('tanggal', '<=', $sampai);
        }

        return [
            'total' => $query->count(),
            'hadir' => (clone $query)->where('status', 'HADIR')->count(),
            'izin' => (clone $query)->where('status', 'IZIN')->count(),
            'sakit' => (clone $query)->where('status', 'SAKIT')->count(),
            'alpa' => (clone $query)->where('status', 'ALPA')->count(),
        ];
    }

    public function getIndikasiMabukCount(int $days = 30): int
    {
        return $this->absensi()
            ->where('tanggal', '>=', now()->subDays($days))
            ->whereHas('indikasi', function ($q) {
                $q->where('final_decision', 'DRUNK INDICATION');
            })
            ->count();
    }

    // ========== SCOPES ==========
    
    public function scopeByKelas($query, string $idKelas)
    {
        return $query->where('id_kelas', $idKelas);
    }

    public function scopeLakiLaki($query)
    {
        return $query->where('jenis_kelamin', 'L');
    }

    public function scopePerempuan($query)
    {
        return $query->where('jenis_kelamin', 'P');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode_siswa', 'like', "%{$search}%")
              ->orWhere('nama_siswa', 'like', "%{$search}%");
        });
    }

    // ========== GLOBAL SCOPES ==========
    
    protected static function booted()
    {
        static::addGlobalScope('guruAccess', function ($query) {
            $user = auth()->user();
            
            if ($user && $user->isGuru()) {
                // Cache the kelas IDs in the request to avoid multiple queries
                $kelasIds = app('request')->get('_guru_kelas_ids');
                
                if ($kelasIds === null) {
                    $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas')->toArray();
                    app('request')->attributes->set('_guru_kelas_ids', $kelasIds);
                }
                
                $query->whereIn('id_kelas', $kelasIds);
            }
        });
    }
}