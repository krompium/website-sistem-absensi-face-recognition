<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    use HasFactory;

    /**
     * ========== PERUBAHAN: PRIMARY KEY & FILLABLE ==========
     */
    protected $primaryKey = 'nip'; // NIP sebagai primary key
    public $incrementing = false; // Karena NIP bukan auto-increment
    protected $keyType = 'string'; // NIP bertipe string

    protected $fillable = [
        'nip',
        'name',
        'username',
        'password',
        'class_id',
        'phone',      // BARU
        'email',      // BARU
        'status',     // BARU
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ========== RELATIONSHIPS ==========
    
    /**
     * ========== PERBAIKAN: RELASI KE CLASSES ==========
     * Sebelumnya: ClassRoom::class (salah)
     * Sekarang: Classes::class (benar)
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // ========== HELPER METHODS ==========
    
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasClass(): bool
    {
        return !is_null($this->class_id);
    }

    public function getClassName(): string
    {
        return $this->class?->name ?? '-';
    }

    public function getFullInfo(): string
    {
        return "{$this->name} (NIP: {$this->nip})";
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeWithClass($query)
    {
        return $query->whereNotNull('class_id');
    }

    public function scopeWithoutClass($query)
    {
        return $query->whereNull('class_id');
    }
}