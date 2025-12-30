<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikasiSiswa extends Model
{
    use HasFactory;

    /**
     * ========== TABEL BARU ==========
     * - Nama tabel: _indikasi_siswa
     * - Data hasil deteksi mabuk dari AI
     * - Terpisah dari tabel absensi
     * - attendance_type: 'in' atau 'out'
     * ================================
     */
    
    protected $table = '_indikasi_siswa';
    protected $primaryKey = 'id_indikasi';
    public $timestamps = false; // Hanya ada created_at
    
    protected $fillable = [
        'id_absensi',
        'session_id',
        'attendance_type',
        'final_decision',
        'frames_used',
        'average_prob_sober',
        'median_prob_sober',
        'face_image',
        'frames_dir',
    ];

    protected $casts = [
        'frames_used' => 'integer',
        'average_prob_sober' => 'decimal:3',
        'median_prob_sober' => 'decimal:3',
    ];

    protected $appends = [
        'decision_label',
        'type_label',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }

    // Accessor melalui absensi
    public function getSiswaAttribute()
    {
        return $this->absensi->siswa ?? null;
    }

    // ========== ACCESSORS ==========
    
    public function getDecisionLabelAttribute(): string
    {
        return match($this->final_decision) {
            'SOBER' => 'Normal/Sadar',
            'DRUNK INDICATION' => 'Terindikasi Mabuk',
            'INCONCLUSIVE' => 'Tidak Dapat Disimpulkan',
            default => 'Unknown',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->attendance_type === 'in' ? 'Masuk' : 'Keluar';
    }

    // ========== HELPER METHODS ==========
    
    public function isSober(): bool
    {
        return $this->final_decision === 'SOBER';
    }

    public function isDrunkIndication(): bool
    {
        return $this->final_decision === 'DRUNK INDICATION';
    }

    public function isInconclusive(): bool
    {
        return $this->final_decision === 'INCONCLUSIVE';
    }

    public function isCheckIn(): bool
    {
        return $this->attendance_type === 'in';
    }

    public function isCheckOut(): bool
    {
        return $this->attendance_type === 'out';
    }

    public function getConfidenceLevel(): string
    {
        $prob = $this->average_prob_sober ?? 0;
        
        if ($prob >= 0.8) {
            return 'very_high'; // Sangat yakin SOBER
        } elseif ($prob >= 0.6) {
            return 'high'; // Yakin SOBER
        } elseif ($prob >= 0.4) {
            return 'medium'; // Ragu-ragu
        } elseif ($prob >= 0.2) {
            return 'low'; // Yakin MABUK
        } else {
            return 'very_low'; // Sangat yakin MABUK
        }
    }

    public function getDecisionBadgeColor(): string
    {
        return match($this->final_decision) {
            'SOBER' => 'success',
            'DRUNK INDICATION' => 'danger',
            'INCONCLUSIVE' => 'warning',
            default => 'secondary',
        };
    }

    public function getRingkasan(): string
    {
        $parts = [];
        
        $parts[] = $this->decision_label;
        $parts[] = "Frames: {$this->frames_used}";
        
        if ($this->average_prob_sober) {
            $persen = round($this->average_prob_sober * 100, 1);
            $parts[] = "Avg: {$persen}%";
        }
        
        if ($this->median_prob_sober) {
            $persen = round($this->median_prob_sober * 100, 1);
            $parts[] = "Median: {$persen}%";
        }

        return implode(' | ', $parts);
    }

    // ========== SCOPES ==========
    
    public function scopeSober($query)
    {
        return $query->where('final_decision', 'SOBER');
    }

    public function scopeDrunkIndication($query)
    {
        return $query->where('final_decision', 'DRUNK INDICATION');
    }

    public function scopeInconclusive($query)
    {
        return $query->where('final_decision', 'INCONCLUSIVE');
    }

    public function scopeCheckIn($query)
    {
        return $query->where('attendance_type', 'in');
    }

    public function scopeCheckOut($query)
    {
        return $query->where('attendance_type', 'out');
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeHariIni($query)
    {
        return $query->whereHas('absensi', function ($q) {
            $q->whereDate('tanggal', today());
        });
    }

    public function scopeHighConfidence($query, float $threshold = 0.7)
    {
        return $query->where(function($q) use ($threshold) {
            $q->where('average_prob_sober', '>=', $threshold)
              ->orWhere('average_prob_sober', '<=', (1 - $threshold));
        });
    }

    // ========== GLOBAL SCOPES ==========
    
    protected static function booted()
    {
        static::addGlobalScope('guruAccess', function ($query) {
            $user = auth()->user();
            
            if ($user && $user->isGuru()) {
                // Use cached kelas IDs from request
                $kelasIds = app('request')->get('_guru_kelas_ids');
                
                if ($kelasIds === null) {
                    $kelasIds = $user->kelasYangDiajar()->pluck('kelas.id_kelas')->toArray();
                    app('request')->attributes->set('_guru_kelas_ids', $kelasIds);
                }
                
                // Use whereHas for better clarity and to avoid ambiguous columns
                $query->whereHas('absensi', function ($q) use ($kelasIds) {
                    $q->whereIn('absensi.id_kelas', $kelasIds);
                });
            }
        });
    }
}