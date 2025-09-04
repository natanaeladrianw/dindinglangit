<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftKasir extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function serahTerimaStoks()
    {
        return $this->hasMany(SerahTerimaStok::class);
    }

    public function laporanPenjualans()
    {
        return $this->hasMany(LaporanPenjualan::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
