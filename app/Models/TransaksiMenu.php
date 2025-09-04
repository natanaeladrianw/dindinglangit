<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMenu extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'transaksi_menus';

    public function menus()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function transaksis()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'id');
    }
}
