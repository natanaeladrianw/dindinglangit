<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBahanBaku extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class);
    }
}
