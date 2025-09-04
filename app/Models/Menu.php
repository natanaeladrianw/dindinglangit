<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function bahanBakuMenus() 
    {
        return $this->hasMany(BahanBakuMenu::class, 'menu_id', 'id');
    }

    public function transaksis()
    {
        return $this->belongsToMany(Transaksi::class, 'transaksi_menus', 'menu_id', 'transaksi_id')
            ->withPivot('jumlah_pesanan', 'catatan_pesanan')
            ->withTimestamps();
    }

    public function bahanBakus()
    {
        return $this->belongsToMany(BahanBaku::class, 'bahan_baku_menu', 'menu_id', 'bahan_baku_id')->withPivot('jml_bahan', 'satuan_id');
    }

    public function kategoriMenus()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id', 'id');
    }
}
