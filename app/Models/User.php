<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function shiftKasirs()
    {
        return $this->hasMany(ShiftKasir::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function stokOpnameGudangs()
    {
        return $this->hasMany(StokOpnameGudang::class);
    }

    public function bahanBakus()
    {
        return $this->belongsToMany(BahanBaku::class, 'penggunaan_bahan_baku', 'user_id', 'bahan_baku_id');
    }

    public function notaKirims()
    {
        return $this->hasMany(NotaKirim::class);
    }
}
