<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupBahanBaku extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'pengajuan' => 'boolean',
    ];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'grup_bahan_baku_id');
    }
    
    public function stokMinimals()
    {
        return $this->hasMany(StokMinimal::class, 'grup_bahan_baku_id');
    }
}
