<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use HasFactory;

    /**
     * ========== STRUKTUR BARU ==========
     * - Nama tabel: kelas
     * - Primary Key: id_kelas (VARCHAR)
     * - No auto-increment
     * - No timestamps (hanya created_at)
     * ===================================
     */
    
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Hanya ada created_at
    
    protected $fillable = [
        'id_kelas',
        'tingkat',
        'jurusan',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'id_kelas', 'id_kelas');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            '_guru_kelas',
            'id_kelas',
            'id_user',
            'id_kelas',
            'id_user'
        )->withTimestamps();
    }

    // ========== HELPER METHODS ==========
    
    public function getFullName(): string
    {
        // Contoh: "XII Rekayasa Perangkat Lunak 2"
        return trim("{$this->tingkat} {$this->jurusan} {$this->urutan}");
    }

    public function getShortName(): string
    {
        // Contoh: "XII RPL 2"
        $jurusanShort = $this->getJurusanShort();
        return trim("{$this->tingkat} {$jurusanShort} {$this->urutan}");
    }

    public function getJurusanShort(): string
    {
        // Ambil inisial jurusan
        $words = explode(' ', $this->jurusan);
        return strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', $words)));
    }

    public function getJumlahSiswa(): int
    {
        return $this->siswa()->count();
    }

    // ========== SCOPES ==========
    
    public function scopeByTingkat($query, string $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    public function scopeByJurusan($query, string $jurusan)
    {
        return $query->where('jurusan', $jurusan);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('tingkat')
                     ->orderBy('jurusan')
                     ->orderBy('urutan');
    }

    /**
     * ACCESSOR BARU: Menggabungkan Tingkat + Jurusan + Urutan
     * Ini memungkinkan kita memanggil $kelas->nama_kelas
     */
    public function getNamaKelasAttribute()
    {
        return "{$this->tingkat} {$this->jurusan} {$this->urutan}";
    }
}