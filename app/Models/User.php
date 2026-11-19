<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User — model pengguna aplikasi.
 *
 * Menyimpan identitas, peran (admin/gizi/dapur/pengawas/akuntan), dan lokasi
 * tugas. Password otomatis di-hash oleh cast "hashed"; field sensitif tidak
 * pernah diserialisasi ke JSON.
 */
#[Fillable(['name', 'email', 'password', 'peran', 'lokasi_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Atribut yang dikonversi otomatis: tanggal verifikasi dan hash password. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
