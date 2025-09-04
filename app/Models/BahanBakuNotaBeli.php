<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBakuNotaBeli extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'bahan_baku_nota_belis';

    public function bahanBakus()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id', 'id');
    }

    public function notaBelis()
    {
        return $this->belongsTo(NotaBeli::class, 'nota_beli_id', 'id');
    }
}
