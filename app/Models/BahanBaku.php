<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function notaKirims()
    {
        return $this->hasMany(NotaKirim::class);
    }

    public function grupBahanBaku()
    {
        return $this->belongsTo(GrupBahanBaku::class, 'grup_bahan_baku_id');
    }

    public function notaBelis()
    {
        return $this->belongsToMany(NotaBeli::class, 'bahan_baku_nota_belis', 'bahan_baku_id', 'nota_beli_id')->withPivot('harga', 'jumlah', 'tgl_exp')->withTimestamps();
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'bahan_baku_menu', 'bahan_baku_id', 'menu_id')->withPivot('jml_bahan', 'satuan_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'penggunaan_bahan_baku', 'bahan_baku_id', 'user_id');
    }

    public function kategoriBahanBakus()
    {
        return $this->belongsTo(KategoriBahanBaku::class, 'kategori_bahan_baku_id', 'id');
    }

    public function bahanBakuNotaBelis()
    {
        return $this->hasMany(BahanBakuNotaBeli::class);
    }

    public function penggunaanBahanBakus()
    {
        return $this->hasMany(PenggunaanBahanBaku::class);
    }

    public function stokDapurs()
    {
        return $this->hasMany(StokDapur::class)->latest();
    }

    public function stokGudangs()
    {
        return $this->hasMany(StokGudang::class)->latest();
    }

    public function stokOpnameGudangs()
    {
        return $this->hasMany(StokOpnameGudang::class);
    }
}
