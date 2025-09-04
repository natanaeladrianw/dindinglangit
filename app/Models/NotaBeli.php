<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaBeli extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function bahanBakus()
    {
        return $this->belongsToMany(BahanBaku::class, 'bahan_baku_nota_belis', 'nota_beli_id', 'bahan_baku_id')->withPivot('harga', 'jumlah', 'tgl_exp')->withTimestamps();
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
