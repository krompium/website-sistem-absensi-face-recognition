<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruKelas extends Model
{
    use HasFactory;

    /**
     * ========== TABEL BARU ==========
     * - Nama tabel: _guru_kelas
     * - Junction table untuk many-to-many
     * - Relasi User (guru) dengan Kelas
     * ================================
     */
    
    protected $table = '_guru_kelas';
    protected $primaryKey = 'id_guru_kelas';
    public $timestamps = false; // Hanya ada created_at
    
    protected $fillable = [
        'id_user',
        'id_kelas',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function guru(): BelongsTo
    {
        return $this->user(); // Alias untuk lebih semantic
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    // ========== HELPER METHODS ==========
    
    public function getNamaGuru(): string
    {
        return $this->user->name ?? '-';
    }

    public function getNamaKelas(): string
    {
        return $this->kelas->getFullName() ?? '-';
    }

    // ========== SCOPES ==========
    
    public function scopeByGuru($query, int $idUser)
    {
        return $query->where('id_user', $idUser);
    }

    public function scopeByKelas($query, string $idKelas)
    {
        return $query->where('id_kelas', $idKelas);
    }
}