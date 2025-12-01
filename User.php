<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Nama tabel secara default adalah 'users', jadi ini tidak perlu ditulis ulang
    protected $primaryKey = 'id_user'; // Sesuaikan dengan kolom primary key di tabel

    public $timestamps = false; // Nonaktifkan kalau tidak pakai created_at dan updated_at

    protected $fillable = [
        'nama_koordinator',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi: satu user punya banyak poster
    public function posters()
    {
        return $this->hasMany(Poster::class, 'id_user');
    }
}
