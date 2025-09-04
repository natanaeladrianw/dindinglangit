<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBakuMenu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'bahan_baku_menus';

    public function grupBahanBaku()
    {
        return $this->belongsTo(GrupBahanBaku::class, 'grup_bahan_baku_id', 'id');
    }

    public function bahanBakus()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id', 'id');
    }

    // Relasi untuk mendapatkan bahan baku melalui grup bahan baku
    public function bahanBakuMelaluiGrup()
    {
        return $this->hasManyThrough(
            BahanBaku::class,
            GrupBahanBaku::class,
            'id', // Foreign key di grup_bahan_bakus
            'grup_bahan_baku_id', // Foreign key di bahan_bakus
            'grup_bahan_baku_id', // Local key di bahan_baku_menus
            'id' // Local key di grup_bahan_bakus
        );
    }

    public function menus()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'id');
    }
}
