<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'anggota';

    // Primary key
    protected $primaryKey = 'id_anggota';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'nama_anggota',
        'nim',
        'id_poster', // Foreign key ke tabel poster
    ];

    /**
     * Relasi ke Poster
     * Satu anggota hanya terkait ke satu poster
     */
    public function poster()
    {
        return $this->belongsTo(Poster::class, 'id_poster');
    }
}
