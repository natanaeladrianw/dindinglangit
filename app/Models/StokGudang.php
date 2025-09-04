<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokGudang extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function bahanBakus()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id', 'id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'id');
    }

    public function notaKirims()
    {
        return $this->hasMany(NotaKirim::class);
    }
}
