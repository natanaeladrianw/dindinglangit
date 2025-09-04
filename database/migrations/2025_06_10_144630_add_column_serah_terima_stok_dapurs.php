<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('serah_terima_stok_dapurs', function (Blueprint $table) {
            $table->foreignId('serah_terima_stok_id')->constrained()->onDelete('cascade');
            $table->foreignId('stok_dapur_id')->constrained()->onDelete('cascade');
            $table->foreignId('satuan_id')->constrained()->onDelete('restrict');
            $table->integer('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serah_terima_stok_dapurs', function (Blueprint $table) {
            //
        });
    }
};
