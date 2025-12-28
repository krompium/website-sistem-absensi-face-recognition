<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ========== PERUBAHAN STRUKTUR ==========
     * - Primary Key: id_user (bukan id)
     * - Role: guru/administrator
     * - Relasi: belongsToMany ke Kelas via _guru_kelas
     * ========================================
     */
    
    protected $primaryKey = 'id_user';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ========== RELATIONSHIPS ==========
    
    public function kelasYangDiajar(): BelongsToMany
    {
        return $this->belongsToMany(
            Kelas::class,
            '_guru_kelas',
            'id_user',
            'id_kelas',
            'id_user',
            'id_kelas'
        )->withTimestamps();
    }

    // ========== HELPER METHODS ==========
    
    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    // ========== SCOPES ==========
    
    public function scopeAdministrators($query)
    {
        return $query->where('role', 'administrator');
    }

    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}