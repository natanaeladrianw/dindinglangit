<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMinimal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function grupBahanBaku()
    {
        return $this->belongsTo(GrupBahanBaku::class, 'grup_bahan_baku_id', 'id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'id');
    }
}


