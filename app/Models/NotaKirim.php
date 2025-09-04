<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaKirim extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function bahanBakus()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function stokGudangs()
    {
        return $this->belongsTo(StokGudang::class, 'stok_gudang_id');
    }

    public function stokDapurs()
    {
        return $this->belongsTo(StokDapur::class, 'stok_dapur_id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
