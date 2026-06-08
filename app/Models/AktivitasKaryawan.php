<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasKaryawan extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_karyawan';

    protected $fillable = [
        'nik',
        'aktivitas',
        'foto',
        'lokasi',
        'poin',
        'tipe_poin',
        'poin_input_by',
        'poin_set_at',
        'poin_original',
        'poin_adjusted_by',
        'poin_adjusted_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'poin_set_at' => 'datetime',
        'poin_adjusted_at' => 'datetime',
        'poin' => 'decimal:2',
        'poin_original' => 'decimal:2',
    ];

    /**
     * Get the karyawan that owns the aktivitas.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    /**
     * Set poin otomatis berdasarkan deskripsi aktivitas dan foto
     * Formula: (word_count / 10) + photo_bonus, max 100
     */
    public function calculateAutomaticPoin()
    {
        $wordCount = str_word_count($this->aktivitas ?? '');
        $hasPhoto = !empty($this->foto) ? 20 : 0;
        
        $poin = min(($wordCount / 10) + $hasPhoto, 100);
        
        $this->poin = round($poin, 2);
        $this->tipe_poin = 'auto';
        $this->poin_set_at = now();
        
        return $this;
    }

    /**
     * Scope untuk mendapatkan aktivitas dalam periode tertentu
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk mendapatkan aktivitas dengan poin
     */
    public function scopeWithPoin($query)
    {
        return $query->where('poin', '>', 0);
    }
}

