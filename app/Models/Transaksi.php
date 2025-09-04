<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function shiftKasirs()
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_kasir_id', 'id');
    }

    public function laporanPenjualans()
    {
        return $this->belongsTo(LaporanPenjualan::class, 'laporan_penjualan_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'transaksi_menus', 'transaksi_id', 'menu_id')
            ->withPivot('jumlah_pesanan', 'catatan_pesanan')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($transaksi) {
            if ($transaksi->isForceDeleting()) {
                // Kalau hard delete (force delete), bisa dihapus beneran
                $transaksi->transaksiMenus()->forceDelete();
            } else {
                // Kalau soft delete, cukup soft delete semua transaksi_menus
                $transaksi->transaksiMenus()->each(function ($transaksiMenu) {
                    $transaksiMenu->delete();
                });
            }
        });
    }
}
