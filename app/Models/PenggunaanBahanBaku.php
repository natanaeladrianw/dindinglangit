<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggunaanBahanBaku extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'penggunaan_bahan_bakus';

    public function bahanBakus()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id', 'id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
