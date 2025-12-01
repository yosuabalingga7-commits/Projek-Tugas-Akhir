<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    use HasFactory;

    protected $table = 'poster';
    protected $primaryKey = 'id_poster';

    protected $fillable = [
        'judul_ta',
        'topik_ta',
        'tahun',
        'program_studi',
        'abstrak',
        'file_poster',
        'pembimbing_1',
        'pembimbing_2',
        'nip_1',
        'nip_2',
        'kota',
        'id_user',
    ];

    // Relasi ke user (Koordinator TA)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi: satu poster punya banyak anggota
    public function anggota()
    {
        return $this->hasMany(Anggota::class, 'id_poster');
    }
}
