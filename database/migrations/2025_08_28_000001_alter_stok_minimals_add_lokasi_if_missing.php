<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stok_minimals', 'lokasi')) {
            Schema::table('stok_minimals', function (Blueprint $table) {
                $table->enum('lokasi', ['dapur', 'gudang'])->default('gudang')->after('satuan_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stok_minimals', 'lokasi')) {
            Schema::table('stok_minimals', function (Blueprint $table) {
                $table->dropColumn('lokasi');
            });
        }
    }
};


